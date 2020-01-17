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

class BulkException extends Exception
{

    /**
     * @var string 
     */
    private $title;

    /**
     * @var array 
     */
    private $message_array;

    /**
     * @var bool 
     */
    private $status;

    /**
     * BulkException constructor.
     * @param string $message
     * @param string $title
     * @param int $code
     * @param bool $status
     * @param Throwable|null $previous
     */
    public function __construct($message = "", string $title = "", int $code = 0,
                                bool $status = false, Throwable $previous = null)
    {
        $is_array = is_array($message);
        parent::__construct(($is_array ? "" : $message), $code, $previous);

        $this->setTitle($title);
        $this->setMessageArray(($is_array ? $message : []));
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
     * @return array
     */
    public function getMessageArray(): array
    {
        return $this->message_array;
    }

    /**
     * @param array $message_array
     */
    private function setMessageArray(array $message_array): void
    {
        $this->message_array = $message_array;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
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