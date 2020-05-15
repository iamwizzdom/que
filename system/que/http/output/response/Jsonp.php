<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 11:26 PM
 */

namespace que\http\output\response;


class Jsonp
{
    /**
     * @var string
     */
    private $callback = 'jsonP';

    /**
     * @var array
     */
    private $data = [
        'status' => false,
        'code' => 0,
        'title' => '',
        'message' => '',
        'response' => [],
        'version' => ''
    ];

    /**
     * @var int
     */
    private $option = 0;

    /**
     * @var int
     */
    private $depth = 512;

    /**
     * Jsonp constructor.
     * @param string $callback
     * @param array $data
     * @param int $option
     * @param int $depth
     */
    public function __construct(string $callback, array $data, int $option = 0, int $depth = 512)
    {
        $this->callback = $callback;
        $this->option = $option;
        $this->depth = $depth;
        $this->data['version'] = config('auth.app.version');
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @return int
     */
    public function getOption(): int
    {
        return $this->option;
    }

    /**
     * @param int $option
     * @return Jsonp
     */
    public function setOption(int $option): Jsonp
    {
        $this->option = $option;
        return $this;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     * @return Jsonp
     */
    public function setDepth(int $depth): Jsonp
    {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallback(): string
    {
        return $this->callback;
    }

    /**
     * @param string $callback
     * @return Jsonp
     */
    public function setCallback(string $callback): Jsonp
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Jsonp
     */
    public function setData(array $data): Jsonp
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return false|string
     */
    public function getJsonp() {
        $json = json_encode($this->data, $this->option, $this->depth);
        if ($json) return "{$this->callback}({$json});";
        return $json;
    }
}