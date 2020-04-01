<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 8:36 PM
 */

namespace que\common\exception;


use Exception;
use Throwable;

class BaseException extends Exception
{

    /**
     * @var string
     */
    private $title;

    /**
     * @var bool
     */
    private $status;

    /**
     * BaseException constructor.
     * @param string $message
     * @param string $title
     * @param int $code
     * @param bool $status
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", string $title = "", int $code = 0,
                                bool $status = false, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setTitle($title);
        $this->setStatus($status);
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
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    private function setStatus(bool $status): void
    {
        $this->status = $status;
    }

}