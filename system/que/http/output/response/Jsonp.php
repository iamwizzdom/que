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
        'response' => []
    ];

    /**
     * @var int
     */
    private $option;

    /**
     * @var int
     */
    private $depth;

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
     */
    public function setOption(int $option): void
    {
        $this->option = $option;
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
     */
    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
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
     */
    public function setCallback(string $callback): void
    {
        $this->callback = $callback;
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
     */
    public function setData(array $data): void
    {
        $this->data = $data;
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