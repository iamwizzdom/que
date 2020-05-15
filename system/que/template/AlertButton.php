<?php

namespace que\template;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/18/2018
 * Time: 4:24 PM
 */

class AlertButton {

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var array
     */
    private $alert = [];

    /**
     * @var mixed
     */
    private $alert_key;

    /**
     * Button constructor.
     * @param Composer $composer
     * @param $alert_key
     */
    public function __construct(Composer $composer, $alert_key)
    {
        $this->composer = $composer;
        $this->alert = $composer->getAlert();
        $this->alert_key = $alert_key;
    }

    /**
     * @param string $title
     * @param string $url
     * @param int $option
     */
    public function button(
        string $title, string $url,
        int $option = ALERT_BUTTON_OPTION_DEFAULT
    ) {
        if ($this->alert_key !== null) {
            $this->alert[$this->alert_key]['button'] = [
                'title' => $title,
                'url' => base_url($url),
                'option' => $option
            ];
        }
        $this->composer->setAlert($this->alert);
    }
}