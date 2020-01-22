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
     * Jsonp constructor.
     * @param string $callback
     * @param array $data
     */
    public function __construct(string $callback, array $data)
    {
        $this->callback = $callback;
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param int $option
     * @param int $depth
     * @return string
     */
    public function getJsonp(int $option = JSON_PRETTY_PRINT, int $depth = 512) {
        return "{$this->callback}(" . json_encode($this->data, $option, $depth) . ");";
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}