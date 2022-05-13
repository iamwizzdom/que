<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/17/2021
 * Time: 10:01 PM
 */

namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\CorsService;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\security\GlobalMiddleware;

class HandleCors extends GlobalMiddleware
{

    /**
     * Add the headers to the Response, if they don't exist yet.
     *
     * @param Input $input
     * @param CorsService $cors
     */
    protected function addHeaders(Input $input, CorsService $cors): void
    {
        if (!$input->getHeader()->has('Access-Control-Allow-Origin')) {
            // Add the CORS headers to the Response
            $cors->addActualRequestHeaders($input);
        }
    }

    private function shouldRun(Input $input): bool
    {
        return $this->isMatchingPath($input);
    }

    private function isMatchingPath(Input $input): bool
    {
        // Get the paths from the config
        $paths = config('cors.paths', []);

        foreach ($paths as $path) {

            if ($path !== '/') {
                $path = trim($path, '/');
            }

            if ($input->getRequest()->fullUrlIs($path) || $input->getRequest()->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        if (!$this->shouldRun($input)) return $next();

        $options = config('cors', []);
        $cors = new CorsService([
            'allowedOrigins' => $options['allowed_origins'] ?? ['*'],
            'allowedOriginsPatterns' => $options['allowed_origins_patterns'] ?? [],
            'supportsCredentials' => $options['supports_credentials'] ?? false,
            'allowedHeaders' => $options['allowed_headers'] ?? ['*'],
            'exposedHeaders' => $options['exposed_headers'] ?? [],
            'allowedMethods' => $options['allowed_methods'] ?? ['*'],
            'maxAge' => $options['max_age'] ?? 0
        ]);

        if ($cors->isPreflightRequest($input)) {
            $cors->handlePreflightRequest($input);
            $cors->varyHeader($input, 'Access-Control-Request-Method');
            throw new QueException("This is a preflight request response.", "Preflight Request", HTTP::NO_CONTENT);
        }

        if (Request::getMethod() === Request::METHOD_OPTIONS) {
            $cors->varyHeader($input, 'Access-Control-Request-Method');
        }

        $this->addHeaders($input, $cors);
        return $next();
    }
}