<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 09/02/2022
 * Time: 8:49 PM
 */

namespace que\error\log;

use Closure;

abstract class LoggerTransport
{
    private string $message;
    private string $file;
    private int $line;
    private array $trace;
    private string $level;
    private string $label;
    private string $type;
    private string $time;
    private int $timestamp;
    private ?string $destination = null;
    private Closure $cliColorCallback;

    public function __construct(Closure $cliColorCallback)
    {
        $this->cliColorCallback = $cliColorCallback;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @param int $line
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    /**
     * @return array
     */
    public function getTrace(): array
    {
        return $this->trace;
    }

    /**
     * @param array $trace
     */
    public function setTrace(array $trace): void
    {
        $this->trace = $trace;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
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
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string|null
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * @param string|null $destination
     */
    public function setDestination(?string $destination): void
    {
        $this->destination = $destination;
    }

    protected function getCLIColor(string $message)
    {
        $callback = $this->cliColorCallback;
        return $callback($message);
    }

    /**
     * @return bool|int
     */
    abstract public function log(): bool|int;
}