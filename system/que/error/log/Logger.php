<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/1/2020
 * Time: 11:06 AM
 */

namespace que\error\log;

use JetBrains\PhpStorm\Pure;

class Logger
{

    /**
     * @var string
     */
    private string $message;

    /**
     * @var string
     */
    private string $file;

    /**
     * @var int
     */
    private int $line;

    /**
     * @var int
     */
    private int $level;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var array
     */
    private array $trace;

    /**
     * @var string
     */
    private string $label;

    /**
     * @var string
     */
    private string $time;

    /**
     * @var int
     */
    private int $timestamp;

    /**
     * @var string|null
     */
    private ?string $destination = null;

    protected function __construct($label)
    {
        $this->type = $label;
        $this->label = ucfirst(APP_PACKAGE_NAME);
        $this->time = date('D, d M Y, h:i:sa T', APP_TIME);
        $this->timestamp = APP_TIME;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message): Logger
    {
        $this->message = $message;
        return $this;
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
     * @return Logger
     */
    public function setFile(string $file): Logger
    {
        $this->file = $file;
        return $this;
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
     * @return Logger
     */
    public function setLine(int $line): Logger
    {
        $this->line = $line;
        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return match ($this->level) {
            E_USER_ERROR => "ERROR [" . E_USER_ERROR . "]",
            E_USER_WARNING => "WARNING [" . E_USER_WARNING . "]",
            E_USER_NOTICE => "NOTICE [" . E_USER_NOTICE . "]",
            default => "UNKNOWN [" . $this->level . "]",
        };
    }

    /**
     * @param int $level
     * @return Logger
     */
    public function setLevel(int $level): Logger
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
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
     * @return Logger
     */
    public function setTrace(array $trace): Logger
    {
        $this->trace = $trace;
        return $this;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return string|null
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * set directory to store logs
     * @param string|null $destination
     * @return Logger
     */
    public function setDestination(?string $destination): Logger
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @param string $message
     * @return string
     */
    #[Pure] private function getCLIColor(string $message): string
    {
        return match ($this->getType()) {
            'error' => "\e[1;31m$message\e[0m",
            'warning' => "\e[1;33m$message\e[0m",
            'info' => "\e[1;32m$message\e[0m",
            'debug' => "\e[0;34m$message\e[0m",
            default => "\e[1;37m$message\e[0m",
        };
    }

    /**
     * @return bool|false|int
     */
    public function log(): bool|int
    {

        $transport = config('log.transport') ?: [];
        $count = 0;

        if (!empty($transport)) {
            foreach ($transport as $logger) {
                $logger = new $logger(function ($message) {
                    return $this->getCLIColor($message);
                });
                if ($logger instanceof LoggerTransport) {
                    $logger->setMessage($this->getMessage());
                    $logger->setFile($this->getFile());
                    $logger->setLine($this->getLine());
                    $logger->setLabel($this->getLabel());
                    $logger->setType($this->getType());
                    $logger->setLevel($this->getLabel());
                    $logger->setTrace($this->getTrace());
                    $logger->setTime($this->getTime());
                    $logger->setTimestamp($this->getTimestamp());
                    $logger->setDestination($this->getDestination());
                    if ($logger->log()) $count++;
                }
            }
        }

        $size = count($transport);
        return $size > 0 && $size == $count;
    }

    /**
     * @return Logger
     */
    #[Pure] public static function error(): Logger
    {
        return new self('error');
    }

    /**
     * @return Logger
     */
    #[Pure] public static function warning(): Logger
    {
        return new self('warning');
    }

    /**
     * @return Logger
     */
    #[Pure] public static function info(): Logger
    {
        return new self('info');
    }

    /**
     * @return Logger
     */
    #[Pure] public static function debug(): Logger
    {
        return new self('debug');
    }

    /**
     * @return Logger
     */
    #[Pure] public static function default(): Logger
    {
        return new self('default');
    }

}
