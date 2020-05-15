<?php


namespace que\route;


class RouteEntry
{
    /**
     * @var array
     */
    public array $uriTokens = [];

    /**
     * @var string
     */
    private string $type = "";

    /**
     * @var string
     */
    private string $uri = "";

    /**
     * @var string
     */
    private string $title = "";

    /**
     * @var string
     */
    private string $module = "";

    /**
     * @var bool
     */
    private ?bool $requireLogIn = null;

    /**
     * @var string
     */
    private ?string $loginUrl = null;

    /**
     * @var bool
     */
    private bool $requireCSRFAuth;

    /**
     * @var bool
     */
    private bool $requireJWTAuth = false;

    /**
     * @var bool
     */
    private bool $underMaintenance = false;

    /**
     * RouteEntry constructor.
     */
    public function __construct()
    {
        $this->setRequireCSRFAuth(config('auth.csrf', false));
    }

    /**
     * @return string
     */
    public function getType(): string
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
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = trim($uri, '/');
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
     * @return string
     */
    public function getModule(): string
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
     * @param string|null $loginUrl
     */
    public function setRequireLogIn(bool $requireLogIn, string $loginUrl = null)
    {
        $this->requireLogIn = $requireLogIn;
        $this->loginUrl = $loginUrl;
    }

    /**
     * @return string
     */
    public function getLoginUrl(): ?string
    {
        return $this->loginUrl;
    }

    /**
     * @return bool
     */
    public function isRequireJWTAuth(): bool
    {
        return $this->requireJWTAuth;
    }

    /**
     * @param bool $requireJWTAuth
     */
    public function setRequireJWTAuth(bool $requireJWTAuth)
    {
        $this->requireJWTAuth = $requireJWTAuth;
    }

    /**
     * @return bool
     */
    public function isRequireCSRFAuth(): bool
    {
        return $this->requireCSRFAuth;
    }

    /**
     * @param bool $requireCSRFAuth
     */
    public function setRequireCSRFAuth(bool $requireCSRFAuth): void
    {
        $this->requireCSRFAuth = $requireCSRFAuth;
    }

    /**
     * @return bool
     */
    public function isUnderMaintenance(): bool
    {
        return $this->underMaintenance;
    }

    /**
     * @param bool $underMaintenance
     */
    public function setUnderMaintenance(bool $underMaintenance): void
    {
        $this->underMaintenance = $underMaintenance;
    }

}