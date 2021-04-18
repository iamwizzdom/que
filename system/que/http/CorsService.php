<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/17/2021
 * Time: 9:18 PM
 */

namespace que\http;


use JetBrains\PhpStorm\Pure;
use que\http\input\Input;
use que\http\request\Request;

class CorsService
{
    private array $options;

    public function __construct(array $options = array())
    {
        $this->options = $this->normalizeOptions($options);
    }

    private function normalizeOptions(array $options = []): array
    {
        $options += [
            'allowedOrigins' => [],
            'allowedOriginsPatterns' => [],
            'supportsCredentials' => false,
            'allowedHeaders' => [],
            'exposedHeaders' => [],
            'allowedMethods' => [],
            'maxAge' => 0
        ];

        // normalize ['*'] to true
        if (in_array('*', $options['allowedOrigins'])) {
            $options['allowedOrigins'] = true;
        }
        if (in_array('*', $options['allowedHeaders'])) {
            $options['allowedHeaders'] = true;
        } else {
            $options['allowedHeaders'] = array_map('strtolower', $options['allowedHeaders']);
        }

        if (in_array('*', $options['allowedMethods'])) {
            $options['allowedMethods'] = true;
        } else {
            $options['allowedMethods'] = array_map('strtoupper', $options['allowedMethods']);
        }

        return $options;
    }

    /**
     * @param Input $input
     * @return bool
     */
    #[Pure] public function isCorsRequest(Input $input): bool
    {
        return $input->getHeader()->has('Origin');
    }

    public function isPreflightRequest(Input $input): bool
    {
        return Request::getMethod() === 'OPTIONS' && $input->getHeader()->has('Access-Control-Request-Method');
    }

    public function handlePreflightRequest(Input $input): void
    {
        http()->http_response_code(HTTP::NO_CONTENT);
        $this->addPreflightRequestHeaders($input);
    }

    public function addPreflightRequestHeaders(Input $input): void
    {
        $this->configureAllowedOrigin($input);

        if ($input->getHeader()->has('Access-Control-Allow-Origin')) {
            $this->configureAllowCredentials($input);

            $this->configureAllowedMethods($input);

            $this->configureAllowedHeaders($input);

            $this->configureMaxAge($input);
        }
    }

    public function isOriginAllowed(Input $input): bool
    {
        if ($this->options['allowedOrigins'] === true) {
            return true;
        }

        if (!$input->getHeader()->has('Origin')) {
            return false;
        }

        $origin = $input->getHeader()->get('Origin');

        if (in_array($origin, $this->options['allowedOrigins'])) {
            return true;
        }

        foreach ($this->options['allowedOriginsPatterns'] as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    public function addActualRequestHeaders(Input $input): void
    {
        $this->configureAllowedOrigin($input);

        if ($input->getHeader()->has('Access-Control-Allow-Origin')) {
            $this->configureAllowCredentials($input);

            $this->configureExposedHeaders($input);
        }
    }

    private function configureAllowedOrigin(Input $input)
    {
        if ($this->options['allowedOrigins'] === true && !$this->options['supportsCredentials']) {
            // Safe+cacheable, allow everything
            $input->getHeader()->set('Access-Control-Allow-Origin', '*');
        } elseif ($this->isSingleOriginAllowed()) {
            // Single origins can be safely set
            $input->getHeader()->set('Access-Control-Allow-Origin', array_values($this->options['allowedOrigins'])[0]);
        } else {
            // For dynamic headers, check the origin first
            if ($input->getHeader()->has('Origin') && $this->isOriginAllowed($input)) {
                $input->getHeader()->set('Access-Control-Allow-Origin', $input->getHeader()->get('Origin'));
            }

            $this->varyHeader($input, 'Origin');
        }
    }

    #[Pure] private function isSingleOriginAllowed(): bool
    {
        if ($this->options['allowedOrigins'] === true || !empty($this->options['allowedOriginsPatterns'])) {
            return false;
        }

        return count($this->options['allowedOrigins']) === 1;
    }

    private function configureAllowedMethods(Input $input)
    {
        if ($this->options['allowedMethods'] === true) {
            $allowMethods = strtoupper($input->getHeader()->get('Access-Control-Request-Method'));
            $this->varyHeader($input, 'Access-Control-Request-Method');
        } else {
            $allowMethods = implode(', ', $this->options['allowedMethods']);
        }

        $input->getHeader()->set('Access-Control-Allow-Methods', $allowMethods);
    }

    private function configureAllowedHeaders(Input $input)
    {
        if ($this->options['allowedHeaders'] === true) {
            $allowHeaders = $input->getHeader()->get('Access-Control-Request-Headers');
            $this->varyHeader($input, 'Access-Control-Request-Headers');
        } else {
            $allowHeaders = implode(', ', $this->options['allowedHeaders']);
        }
        $input->getHeader()->set('Access-Control-Allow-Headers', $allowHeaders);
    }

    private function configureAllowCredentials(Input $input)
    {
        if ($this->options['supportsCredentials']) {
            $input->getHeader()->set('Access-Control-Allow-Credentials', 'true');
        }
    }

    private function configureExposedHeaders(Input $input)
    {
        if ($this->options['exposedHeaders']) {
            $input->getHeader()->set('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
        }
    }

    private function configureMaxAge(Input $input)
    {
        if ($this->options['maxAge'] !== null) {
            $input->getHeader()->set('Access-Control-Max-Age', (int) $this->options['maxAge']);
        }
    }

    public function varyHeader(Input $input, $header): void
    {
        if (!$input->getHeader()->has('Vary')) {
            $input->getHeader()->set('Vary', $header);
        } elseif (!in_array($header, explode(', ', $input->getHeader()->get('Vary')))) {
            $input->getHeader()->set('Vary', $input->getHeader()->get('Vary') . ', ' . $header);
        }
    }

    private function isSameHost(Input $input): bool
    {
        return $input->getHeader()->get('Origin') === $input->getRequest()->getSchemeAndHttpHost();
    }
}