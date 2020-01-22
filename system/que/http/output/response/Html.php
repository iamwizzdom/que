<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 11:26 PM
 */

namespace que\http\output\response;


use tidy;

class Html
{
    /**
     * @var string
     */
    private $data = '';

    /**
     * Html constructor.
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * @param array $config
     * @param string $encoding
     * @return tidy
     */
    public function getHtml(array $config = [
        'indent' => true,
        'output-xhtml' => true,
        'wrap' => true
    ], string $encoding = 'utf8'): tidy {

        $tidy = new tidy;
        $tidy->parseString($this->data, $config, $encoding);
        $tidy->cleanRepair();
        return $tidy;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}