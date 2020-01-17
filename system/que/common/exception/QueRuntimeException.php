<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/24/2019
 * Time: 1:09 PM
 */

namespace que\common\exception;

use RuntimeException;
use Throwable;

class QueRuntimeException extends RuntimeException
{

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $http_code;

    /**
     * QueRuntimeException constructor.
     * @param string $message
     * @param string $title
     * @param int $code
     * @param int $http_code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", string $title = "", int $code = 0,
                                int $http_code = HTTP_INTERNAL_ERROR_CODE,
                                Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setTitle($title);
        $this->setHttpCode($http_code);
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
    private function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    /**
     * @param int $http_code
     */
    private function setHttpCode(int $http_code): void
    {
        $this->http_code = $http_code;
    }
}