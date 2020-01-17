<?php


namespace que\route\structure;


class RouteEntry
{
    /**
     * @var array
     */
    public $uriTokens = [];

    /**
     * @var string
     */
    private $type = "";

    /**
     * @var string
     */
    private $uri = "";

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var string
     */
    private $module = "";

    /**
     * @var int
     */
    private $implement = 0;

    /**
     * @var bool
     */
    private $requireLogIn = false;

    /**
     * @var string
     */
    private $loginUrl = "";

    /**
     * @var bool
     */
    private $requireCSRFAuth = CSRF;

    /**
     * @var bool
     */
    private $requireJWTAuth = false;

    /**
     * @var bool
     */
    private $underMaintenance = false;

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
        $this->uri = strlen($uri) > 1 && str_ends_with($uri, '/') ? rtrim($uri, '/') : $uri;
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
     * @return int
     */
    public function getImplement(): int
    {
        return $this->implement;
    }

    /**
     * @param int $implement
     */
    public function setImplement(int $implement)
    {
        $this->implement = $implement;
    }

    /**
     * @return bool
     */
    public function isRequireLogIn(): bool
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
    public function getLoginUrl(): string
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