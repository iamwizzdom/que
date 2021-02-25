<?php


namespace que\route;


use que\http\request\Request;

class RouteEntry
{
    /**
     * @var array
     */
    public array $uriTokens = [];

    /**
     * @var array
     */
    private array $allowedMethods = [];

    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string
     */
    private string $type = "";

    /**
     * @var string|null
     */
    private ?string $uri = "";

    /**
     * @var string|null
     */
    private ?string $title = null;

    /**
     * @var string|null
     */
    private ?string $description = null;

    /**
     * @var string|null
     */
    private ?string $contentType = null;

    /**
     * @var string|null
     */
    private ?string $module = null;

    /**
     * @var bool
     */
    private ?bool $requireLogin = null;

    /**
     * @var string|null
     */
    private ?string $redirectUrl = null;

    /**
     * @var bool
     */
    private bool $forbidCSRF;

    /**
     * @var array
     */
    private array $IgnoredCRSFRequestMethods = [];

    /**
     * @var bool
     */
    private bool $underMaintenance = false;

    /**
     * @var string[]
     */
    private array $middleware = [];

    /**
     * @var string|null
     */
    private ?string $moduleMethod = null;

    /**
     * RouteEntry constructor.
     */
    public function __construct()
    {
        $this->forbidCSRF = (bool) config('auth.csrf', false);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri == "/" ? $uri : trim($uri, '/');
        $this->uriTokens = Router::tokenizeUri($uri);
    }

    /**
     * @param string $argName
     * @param string|null $expression
     * @param bool|null $nullable
     * @return $this
     */
    public function where(string $argName, ?string $expression, bool $nullable = null) {
        foreach ($this->uriTokens as $key => $arg) {
            if (preg_match("/{(.*?)}/", $arg) == 1) {

                $decipheredArg = Router::decipherArg($arg);

                if ($decipheredArg['arg'] == $argName) {

                    if ($decipheredArg['nullable'] && $nullable === null) $nullable = true;

                    $this->uriTokens[$key] = ($nullable === true ? '?' : '') . $argName . (!empty($expression) ? ":{$expression}" : '');
                    $this->uriTokens[$key] = "{" . $this->uriTokens[$key] . "}";
                }

            }
        }
        $this->uri = implode('/', $this->uriTokens);
        return $this;
    }

    /**
     * @return array
     */
    public function getUriTokens(): array
    {
        return $this->uriTokens;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType ?: mime_type_from_extension('html');
    }

    /**
     * @param string $contentType
     */
    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @param string $extension
     */
    public function setContentTypeByExtension(string $extension): void
    {
        $this->contentType = mime_type_from_extension($extension);
    }

    /**
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule(string $module)
    {
        $this->module = $module;
    }

    /**
     * @return string|null
     */
    public function getModuleMethod(): ?string
    {
        return $this->moduleMethod;
    }

    /**
     * @param string|null $moduleMethod
     */
    public function setModuleMethod(?string $moduleMethod): void
    {
        $this->moduleMethod = $moduleMethod;
    }

    /**
     * @return bool|null
     */
    public function isRequireLogin(): ?bool
    {
        return $this->requireLogin;
    }

    /**
     * @param bool $requireLogin
     * @param string|null $redirectUrl
     */
    public function requireLogin(bool $requireLogin, string $redirectUrl = null)
    {
        $this->requireLogin = $requireLogin;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @return bool
     */
    public function isForbidCSRF(): bool
    {
        return $this->forbidCSRF;
    }

    /**
     * @param bool $status
     * @param string[] $ignoredRequestMethods
     */
    public function forbidCSRF(bool $status = true, array $ignoredRequestMethods = null): void
    {
        $this->forbidCSRF = $status;
        if ($ignoredRequestMethods) {
            array_callback($ignoredRequestMethods, function ($value) {
                return strtoupper($value);
            });
            $this->IgnoredCRSFRequestMethods = $ignoredRequestMethods;
        }
    }

    /**
     * @return array
     */
    public function getIgnoredCRSFRequestMethods(): array
    {
        return $this->IgnoredCRSFRequestMethods;
    }

    /**
     * @return bool
     */
    public function isUnderMaintenance(): bool
    {
        return $this->underMaintenance;
    }

    public function underMaintenance(): void
    {
        $this->underMaintenance = true;
    }

    /**
     * @return string[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param string|string[] $middleware
     */
    public function setMiddleware(array|string $middleware): void
    {
        if (!is_array($middleware)) $middleware = [(string) $middleware];
        $this->middleware = $middleware;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @return RouteEntry
     */
    public function allowGetRequest(): RouteEntry
    {
        $this->allowedMethods[] = Request::METHOD_GET;
        return $this;
    }

    /**
     * @return RouteEntry
     */
    public function allowPostRequest(): RouteEntry
    {
        $this->allowedMethods[] = Request::METHOD_POST;
        return $this;
    }

    /**
     * @return RouteEntry
     */
    public function allowPutRequest(): RouteEntry
    {
        $this->allowedMethods[] = Request::METHOD_PUT;
        return $this;
    }

    /**
     * @return RouteEntry
     */
    public function allowPatchRequest(): RouteEntry
    {
        $this->allowedMethods[] = Request::METHOD_PATCH;
        return $this;
    }

    /**
     * @return RouteEntry
     */
    public function allowDeleteRequest(): RouteEntry
    {
        $this->allowedMethods[] = Request::METHOD_DELETE;
        return $this;
    }

}
