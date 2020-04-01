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
     * Json constructor.
     * @param array $data
     * @param int $option
     * @param int $depth
     */
    public function __construct(array $data, int $option = 0, int $depth = 512)
    {
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
     * @return Json
     */
    public function setOption(int $option): Json
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
     * @return Json
     */
    public function setDepth(int $depth): Json
    {
        $this->depth = $depth;
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
     * @return Json
     */
    public function setData(array $data): Json
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return false|string
     */
    public function getJson() {
        return json_encode($this->data, $this->option, $this->depth);
    }
}