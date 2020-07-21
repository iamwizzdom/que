<?php


namespace que\route;


use Exception;
use que\common\exception\QueException;
use que\common\exception\RouteException;
use que\http\Http;
use que\http\input\Input;
use que\security\CSRF;
use que\security\JWT\TokenEncoded;
use que\utility\random\UUID;

class RouteInspector
{

    /**
     * @var RouteInspector
     */
    private static RouteInspector $instance;

    /**
     * @var RouteEntry
     */
    private RouteEntry $routeEntry;

    /**
     * @var array
     */
    private array $uriTokens;

    /**
     * @var array
     */
    private array $foundRoutes = [];

    /**
     * RouteRegistrar constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return RouteInspector
     */
    public static function getInstance(): RouteInspector
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param RouteEntry $routeEntry
     */
    public function setRouteEntry(RouteEntry $routeEntry): void
    {
        $this->routeEntry = $routeEntry;
    }

    /**
     * @param array $uriTokens
     */
    public function setUriTokens(array $uriTokens): void
    {
        $this->uriTokens = $uriTokens;
    }

    /**
     * @return array
     */
    public function getFoundRoutes(): array
    {
        return $this->foundRoutes;
    }

    /**
     * This method inspects a registered route to see if it closely matches the current uri by at least by 50%
     */
    public function inspect()
    {

        $percentage = self::routeMatchPercentage($this->routeEntry, $this->uriTokens,
            ($routeArgs = self::getRouteArgs($this->routeEntry->getUri())));

        if ($percentage < 50) return;

        if (($uriTokenSize = array_size($this->uriTokens)) > ($routeUriTokenSize = array_size($this->routeEntry->uriTokens))) {

            $this->foundRoutes[] = [
                'args' => [],
                'routeEntry' => $this->routeEntry,
                'percentage' => $percentage,
                'error' => "You are passing more arguments than required by the current route",
                'code' => HTTP_UNAUTHORIZED
            ];
            return;
        }

        if ($uriTokenSize < $routeUriTokenSize) {
            $this->foundRoutes[] = [
                'args' => [],
                'routeEntry' => $this->routeEntry,
                'percentage' => $percentage,
                'error' => "You are passing fewer arguments than required by the current route",
                'code' => HTTP_UNAUTHORIZED
            ];
            return;
        }

        $foundArgs = [];

        if (!empty($routeArgs)) {

            foreach ($routeArgs as $routeArg) {

                if (empty($routeArg)) {
                    $this->foundRoutes[] = [
                        'args' => [],
                        'routeEntry' => $this->routeEntry,
                        'percentage' => $percentage,
                        'error' => "Invalid route argument",
                        'code' => HTTP_UNAUTHORIZED
                    ];
                    return;
                }

                $routeArgList = [$routeArg];

                if (str_contains($routeArg, ":"))
                    $routeArgList = explode(":", $routeArg, 2);

                $key = array_search('{' . $routeArg . '}', $this->routeEntry->uriTokens);

                if (!isset($this->uriTokens[$key])) {
                    $this->foundRoutes[] = [
                        'args' => [],
                        'routeEntry' => $this->routeEntry,
                        'percentage' => $percentage,
                        'error' => "Expected uri argument not found in the current route",
                        'code' => HTTP_EXPECTATION_FAILED
                    ];
                    return;
                }

                $uriArgValue = $this->uriTokens[$key];

                if (isset($routeArgList[1]) && strcmp($routeArgList[1], "any") != 0) {
                    try {
                        $this->validateArgDataType($routeArgList[1], $uriArgValue);
                    } catch (RouteException $e) {
                        $this->foundRoutes[] = [
                            'args' => [],
                            'routeEntry' => $this->routeEntry,
                            'percentage' => $percentage,
                            'error' => $e->getMessage(),
                            'code' => $e->getCode()
                        ];
                        return;
                    }
                }

                $foundArgs[$routeArgList[0]] = $uriArgValue;
            }
        }

        $this->foundRoutes[] = [
            'args' => $foundArgs,
            'routeEntry' => $this->routeEntry,
            'percentage' => $percentage,
            'error' => null,
            'code' => HTTP_OK
        ];
    }

    /**
     * @param RouteEntry $entry
     * @param array $uriTokens
     * @param array $routeArgs
     * @return float|int
     */
    public static function routeMatchPercentage(RouteEntry $entry, array $uriTokens, array $routeArgs) {

        $size_1 = array_size($uriTokens);
        $size_2 = array_size($entry->uriTokens);

        if ($size_1 == 0 && $size_2 == 0) return 100;

        foreach ($routeArgs as $arg) {
            $key = array_search('{' . $arg . '}', $entry->uriTokens);
            if (array_key_exists($key, $uriTokens))
                $uriTokens[$key] = $entry->uriTokens[$key];
        }

        $match = 0;

        foreach ($entry->uriTokens as $key => $token) {
            if (strcmp($token, $uriTokens[$key] ?? null) != 0) break;
            $match++;
        }

        if ($match == 0) return 0;

        return ($match * 100) / ($size_1 > $size_2 ? $size_1 : $size_2);
    }

    /**
     * @param string $uri
     * @return array
     */
    public static function getRouteArgs(string $uri)
    {
        if (preg_match_all("/\{(.*?)\}/", $uri, $matches)) {
            return array_map(function ($m) {
                return trim($m, '?');
            }, $matches[1]);
        }
        return [];
    }

    /**
     * @param $regex
     * @param $value
     * @throws RouteException
     */
    public static function validateArgDataType(string $regex, $value) {

        if (strcmp($regex, "uuid") == 0) {

            if (UUID::is_valid($value)) return;

            $value = str_ellipsis($value ?? '', 70);
            throw new RouteException(
                "Invalid data type found in route argument [Arg: {$value}]",
                "Route Error", HTTP_EXPECTATION_FAILED
            );

        } elseif (strcmp($regex, "num") == 0) $regex = "/^[0-9]+$/";
        elseif (strcmp($regex, "alpha") == 0) $regex = "/^[a-zA-Z]+$/";

        if (!preg_match($regex, $value)) {
            $value = str_ellipsis($value ?? '', 70);
            throw new RouteException(
                "Invalid data type found in route argument [Arg: {$value}]",
                "Route Error", HTTP_EXPECTATION_FAILED
            );
        }
    }

    /**
     * @throws RouteException
     */
    public static function validateCSRF() {

        $token = Input::getInstance()->get('X-Csrf-Token');
        if (empty($token)) {
            foreach (
                [
                    'X-CSRF-TOKEN',
                    'x-csrf-token',
                    'csrf',
                    'Csrf',
                    'CSRF'
                ] as $key
            ) {
                $token = Input::getInstance()->get($key);
                if (!empty($token)) break;
            }
        }

        try {

            CSRF::getInstance()->validateToken((!is_null($token) ? $token : ""));
            CSRF::getInstance()->generateToken();

        } catch (QueException $e) {

            CSRF::getInstance()->generateToken();
            throw new RouteException($e->getMessage(), $e->getTitle(), HTTP_EXPIRED_AUTH);
        }

    }

    /**
     * @param Http $http
     * @throws RouteException
     */
    public static function validateJWT(Http $http) {
        try {

            $token = get_bearer_token();
            if (empty($token)) {
                foreach (
                    [
                        'X-JWT-TOKEN',
                        'X-Jwt-Token',
                        'x-jwt-token',
                        'jwt',
                        'Jwt',
                        'JWT'
                    ] as $key
                ) {
                    $token = Input::getInstance()->get($key);
                    if (!empty($token)) break;
                }
            }

            $tokenEncoded = new TokenEncoded($token);
            $tokenEncoded->validate(config('auth.jwt.key', ''), config('auth.jwt.algo'));
            $tokenDecoded = $tokenEncoded->decode();
            $http->_server()->offsetSet("JWT_PAYLOAD", $tokenDecoded->getPayload());
            $http->_server()->offsetSet("JWT_HEADER", $tokenDecoded->getHeader());

        } catch (Exception $e) {
            throw new RouteException($e->getMessage(), "JWT Auth Error", HTTP_EXPIRED_AUTH);
        }
    }

    /**
     * @param string $method
     * @return bool
     */
    public static function isSupportedMethod(string $method): bool
    {
        if (!preg_match('/^[a-z-A-Z]+$/', $method)) return false;

        switch (strtoupper($method)) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                return true;
            default:
                return false;
        }
    }
}