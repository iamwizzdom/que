<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 11:26 PM
 */

namespace que\http\output\response;


class Json
{

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
     * Json constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param int $option
     * @param int $depth
     * @return false|string
     */
    public function getJson(int $option = JSON_PRETTY_PRINT, int $depth = 512) {
        return json_encode($this->data, $option, $depth);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}