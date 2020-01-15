<?php


namespace que\common\exception;

use Exception;
use Throwable;

class RouteException extends Exception
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $trace;

    /**
     * @var string
     */
    private $traceAsString;

    /**
     * @var Throwable
     */
    private $previous;

    public function __construct($message = "", $title = "Route Error", $code = 0, Throwable $previous = null)
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