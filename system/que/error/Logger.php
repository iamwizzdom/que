<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/1/2020
 * Time: 11:06 AM
 */

namespace que\error;


use que\common\exception\QueException;
use que\http\request\Request;
use que\utility\client\IP;

class Logger
{

    /**
     * @var mixed
     */
    private $message;

    /**
     * @var string
     */
    private string $file;

    /**
     * @var int
     */
    private int $line;

    /**
     * @var mixed
     */
    private $level;

    /**
     * @var mixed
     */
    private $status;

    /**
     * @var array
     */
    private array $trace;

    /**
     * @var string|null
     */
    private ?string $destination = null;


    /**
     * @return mixed
     */
    public function getMessage()
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
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param $level
     * @return Logger
     */
    public function setLevel($level): Logger
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Logger
     */
    public function setStatus($status): Logger
    {
        $this->status = $status;
        return $this;
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
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * set directory to store logs
     * @param string $destination
     * @return Logger
     */
    public function setDestination(?string $destination): Logger
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return bool|false|int
     */
    public function log() {

        $error = [
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'status' => $this->getStatus(),
            'level' => $this->getLevel(),
            'trace' => $this->getTrace(),
            'time' => [
                'int' => APP_TIME,
                'readable' => date('h:i:s a, F jS, Y', APP_TIME)
            ],
            'request' => [
                'url' => current_url(),
                'method' => Request::getMethod(),
                'ip' => IP::real()
            ]
        ];

        if (empty($this->getDestination()))
            $this->setDestination(config('log.error.path') ?? QUE_PATH . "/cache/error");

        $destination = rtrim($this->getDestination(), '/');

        if (!is_dir($destination)) {
            try {
                mk_dir($destination);
                sleep(1);
            } catch (QueException $e) {
                return false;
            }
        }

        $filename = (config("log.error.filename") ??  "que-log") . "-" . date("Y-m-d") . ".json";
        $previous_errors = is_file("{$destination}/{$filename}") ? json_decode(file_get_contents(
            "{$destination}/{$filename}") ?: "{}", true) : [];
        array_unshift($previous_errors, $error);
        return file_put_contents("{$destination}/{$filename}", json_encode($previous_errors, JSON_PRETTY_PRINT));
    }

    /**
     * @return Logger
     */
    public static function getInstance(): Logger
    {
        return new self;
    }

}
