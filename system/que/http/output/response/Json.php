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
    const DEFAULT_OPTION = 0;
    const DEFAULT_DEPTH = 512;

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
    private int $option = self::DEFAULT_OPTION;

    /**
     * @var int
     */
    private int $depth = self::DEFAULT_DEPTH;

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
        return self::encode($this->data, $this->option, $this->depth);
    }

    /**
     * @param $data
     * @param int $option
     * @param int $depth
     * @return false|string
     */
    public static function encode($data, int $option = self::DEFAULT_OPTION, int $depth = self::DEFAULT_DEPTH) {
        return json_encode($data, $option, $depth);
    }
}