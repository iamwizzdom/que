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
     * @var string
     */
    private ?string $name = null;

    /**
     * @var string
     */
    private string $type = "";

    /**
     * @var string
     */
    private ?string $uri = null;

    /**
     * @var string
     */
    private ?string $title = null;

    /**
     * @var string
     */
    private ?string $description = null;

    /**
     * @var string
     */
    private ?string $module = null;

    /**
     * @var bool
     */
    private ?bool $requireLogIn = null;

    /**
     * @var string
     */
    private ?string $redirectUrl = null;

    /**
     * @var bool
     */
    private bool $forbidCSRF = false;

    /**
     * @var bool
     */
    private bool $underMaintenance = false;

    /**
     * @var string
     */
    private ?string $middleware = null;

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
     * @return bool|null
     */
    public function isRequireLogIn(): ?bool
    {
        return $this->requireLogIn;
    }

    /**
     * @param bool $requireLogIn
     * @param string|null $redirectUrl
     */
    public function requireLogIn(bool $requireLogIn, string $redirectUrl = null)
    {
        $this->requireLogIn = $requireLogIn;
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
     */
    public function forbidCSRF(bool $status = true): void
    {
        $this->forbidCSRF = $status;
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
     * @return string|null
     */
    public function getMiddleware(): ?string
    {
        return $this->middleware;
    }

    /**
     * @param string $middleware
     */
    public function setMiddleware(string $middleware): void
    {
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
