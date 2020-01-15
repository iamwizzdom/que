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

class QueException extends Exception
{

    /**
     * @var string
     */
    private $title = 'Que Error';

    /**
     * QueException constructor.
     * @param string $message
     * @param string $title
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", string $title = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setTitle($title);
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

}