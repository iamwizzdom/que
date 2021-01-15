<?php


namespace que\route;


use Exception;
use que\common\exception\PreviousException;
use que\common\exception\RouteException;
use que\http\HTTP;
use que\support\Arr;

abstract class Router extends RouteInspector
{
    use RouteCompiler;

    /**
     * @return RouteEntry|null
     */
    public static function getCurrentRoute() {
        return http()->_server()->get('route.entry');
    }

    /**
     * @return array|null
     */
    public static function getRouteParams() {
        return server('route.params');
    }


    /**
     * @param string $routeName
     * @param array $args
     * @param bool $addBaseUrl
     * @return string
     * @throws RouteException
     */
    public static function getRouteUrl(string $routeName, array $args = [], bool $addBaseUrl = true): string
    {
        $routeEntry = self::getRouteEntryFromName($routeName);
        if (!$routeEntry instanceof RouteEntry) throw new RouteException("Route [{$routeName}] not found", "Route Error",
           HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        if ($routeEntry->isRequireLogin() === true && !is_logged_in()) return '#';
        if ($routeEntry->isRequireLogin() === false && is_logged_in()) return '#';

        $uri = $routeEntry->getUri();

        if (self::routeHasArgs($routeEntry)) {

            $routeArgs = self::getRouteArgs($routeEntry);

            $tokens = $routeEntry->getUriTokens();

            foreach ($routeArgs as $argKey => $arg) {

                $decipheredArg = self::decipherArg("{" . $arg . "}");

                if (empty($decipheredArg)) continue;

                if (!Arr::exists($args, $decipheredArg['arg'])) throw new RouteException(
                    "Missing required parameter for [URI: {$uri}] [Param: {$decipheredArg['arg']}] [Route: {$routeEntry->getName()}]", "Route Error");

                $key = array_search("{" . $arg . "}", $tokens);

                if (!empty($decipheredArg['expression'])) {

                    try {
                        self::validateArgDataType($decipheredArg['expression'], $args[$decipheredArg['arg']], $key, $decipheredArg['nullable']);
                    } catch (RouteException $e) {
                        throw new RouteException(
                            "{$e->getMessage()} \n[URI: {$uri}] [Param: {$decipheredArg['arg']}, '{$args[$decipheredArg['arg']]}'] [Route: {$routeEntry->getName()}]", $e->getTitle());
                    }

                    $tokens[$key] = $args[$decipheredArg['arg']];

                } else $tokens[$key] = $args[$decipheredArg['arg']];
            }

            $uri = implode('/', $tokens);
        }

        return $addBaseUrl ? base_url($uri) : $uri;
    }

    /**
     * @param string $name
     * @return RouteEntry|null
     */
    public static function getRouteEntryFromName(string $name): ?RouteEntry
    {
        $routeEntries = self::getRouteEntries();
        foreach ($routeEntries as $routeEntry) {
            if (strcmp($routeEntry->getName(), $name) == 0) return $routeEntry;
        }
        return null;
    }

    /**
     * @param string $uri
     * @param string|null $type
     * @return RouteEntry|null
     * @throws RouteException
     */
    public static function getRouteEntryFromUri(string $uri, string $type = null): ?RouteEntry {
        if (str__contains($uri, $base = base_url())) $uri = trim(str_start_from($uri, $base), '/');
        return self::resolveRoute($uri, $type);
    }

    /**
     * Here we resolve the registered route for the current uri if any
     *
     * @param string $uri
     * @param string|null $type
     * @return RouteEntry|null
     * @throws RouteException
     */
    protected static function resolveRoute(string $uri, string $type = null): ?RouteEntry
    {

        self::compile(); // Compile registered routes
        $routeEntries = self::getRouteEntries();
        $uriTokens = self::tokenizeUri($uri);
        $container = null;

        foreach ($routeEntries as $routeEntry) {

            if ($type !== null && $type != $routeEntry->getType()) continue;

            if (self::matchTokens($uriTokens, $routeEntry->getUriTokens())) {
                if (!self::getCurrentRoute()) {
                    self::setCurrentRoute($routeEntry);
                    self::setRouteParams([]);
                }
                return $routeEntry;
            }

            if (!self::routeHasArgs($routeEntry)) continue;

            // The uri wasn't exact at this level, there might be some arguments in the uri, lets see.
            $routeArgs = self::getRouteArgs($routeEntry);

            $failures = []; $args = [];

            $tokens = $routeEntry->getUriTokens();

            foreach ($routeArgs as $argKey => $arg) {

                $decipheredArg = self::decipherArg("{" . $arg . "}");

                if (empty($decipheredArg)) continue;

                $key = array_search("{" . $arg . "}", $tokens);

                if (!empty($decipheredArg['expression'])) {
                    try {
                        self::validateArgDataType($decipheredArg['expression'], $key !== false ? ($uriTokens[$key] ?? null) : null, $key, $decipheredArg['nullable']);
                    } catch (RouteException $e) {
                        $failures[$key] = $e->getMessage();
                    }
                }

                $args[$decipheredArg['arg']] = ($key !== false ? ($uriTokens[$key] ?? null) : null);
                $tokens[$key] = $args[$decipheredArg['arg']] ?: '--';
                $uriTokens[$key] = $tokens[$key];
            }

            $setRoute = false;

            try {

                if (self::matchTokens($tokens, Arr::extract($uriTokens, 0, (($size1 = Arr::size($tokens)) - 1)))) {

                    if (!self::getCurrentRoute()) {
                        self::setCurrentRoute($routeEntry);
                        self::setRouteParams($args);
                        $setRoute = true;
                    }

                    if ($size1 == ($size2 = Arr::size($uriTokens)) && !empty($failures))
                        throw new RouteException(implode(",\n ", $failures), "Route Error", HTTP::UNAUTHORIZED);


                    if ($size2 > $size1)
                        throw new RouteException("You are passing more arguments than required by the current route",
                        "Route Error", HTTP::UNAUTHORIZED);

                    return $routeEntry;

                } elseif (self::matchTokens($uriTokens, Arr::extract($tokens, 0, (Arr::size($uriTokens) - 1)))) {
                    throw new RouteException("You are passing fewer arguments than required by the current route",
                        "Route Error", HTTP::UNAUTHORIZED);
                }

                foreach ($uriTokens as $key => $uri) if ($uri === '--') unset($uriTokens[$key]);

            } catch (Exception $exception) {
                if ($setRoute) {
                    self::setCurrentRoute(null);
                    self::setRouteParams([]);
                }
                $container = [$exception->getMessage(), $routeEntry];
            }

        }

        if ($container !== null) {
            list($error, $entry) = $container;
            if (!self::getCurrentRoute()) self::setCurrentRoute($entry);
            throw new RouteException($error, "Route Error", HTTP::UNAUTHORIZED);
        }

        return null;
    }

    /**
     * @param RouteEntry|null $routeEntry
     */
    protected static function setCurrentRoute(?RouteEntry $routeEntry)
    {
        http()->_server()->set('route.entry', $routeEntry);
    }

    /**
     * @param array $params
     */
    protected static function setRouteParams(array $params) {
        http()->_server()->set("route.params", $params);
    }
}
