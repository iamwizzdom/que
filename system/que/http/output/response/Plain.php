<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 11:45 PM
 */

namespace que\http\output\response;


class Plain
{

    /**
     * @var string
     */
    private $data = '';

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $data
     * @return Plain
     */
    public function setData(string $data): Plain
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}