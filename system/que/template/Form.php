<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/24/2020
 * Time: 6:11 PM
 */

namespace que\template;


class Form
{
    /**
     * @var Form
     */
    private static $instance;

    /**
     * @var string
     */
    private $formAction = '';

    /**
     * @var string
     */
    private $formMethod = '';

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Form
     */
    public static function getInstance(): Form
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $action
     * @param array $attributes
     * @param bool $multipart
     * @return string
     */
    function formOpen(string $action = '', array $attributes = [], bool $multipart = false)
    {

        if (empty($action)) $action = '#';

        if ($action != "#" && !str_contains($action, "://"))
            $action = base_url($action);

        if (!in_array('method', $keys = array_keys($attributes)))
            $attributes['method'] = 'post';

        if ($multipart === true && !in_array('enctype', $keys))
            $attributes['enctype'] = 'multipart/form-data';

        $this->formAction = $action;
        $this->formMethod = $attributes['method'];

        return "<form action='{$action}' {$this->attributesToString($attributes)}>\n";
    }

    /**
     * @return string
     */
    public function formClose() {

        $currentRoute = current_route();

        $formClose = '';

        if (($currentRoute && $currentRoute->isRequireCSRFAuth()) &&
            ($this->formAction == "#" || str_contains($this->formAction, base_url())) &&
            strtolower($this->formMethod) != "get")
        {
            $formClose .= "<input type='hidden' name='csrf' value='{$this->getCsrfToken()}'/>\n";
        }

        $formClose .= "<input type='hidden' name='track' value='{$this->getTrackToken()}'/>\n";

        return "{$formClose}</form>\n";
    }

    /**
     * @param string $tagName
     * @param $value
     * @param array $attributes
     * @return string
     */
    public function formElement(string $tagName, $value, array $attributes = []) {

        $elem = '';

        switch ($tagName) {
            case 'input':
                if (isset($attributes['value'])) unset($attributes['value']);
                $elem = "<input value='{$value}' {$this->attributesToString($attributes)} />\n";
                break;
            case 'textarea':
                $elem = "<textarea {$this->attributesToString($attributes)} >{$value}</textarea>\n";
                break;
            case 'button':
                $elem = "<button {$this->attributesToString($attributes)} >{$value}</button>\n";
                break;
            case 'label':
                $elem = "<label {$this->attributesToString($attributes)} >{$value}</label>\n";
                break;
            case 'fieldset':
                $elem = "<fieldset {$this->attributesToString($attributes)} >{$value}</fieldset>\n";
                break;
            case 'legend':
                $elem = "<legend {$this->attributesToString($attributes)} >{$value}</legend>\n";
                break;
            case 'datalist':
                $elem = "<datalist {$this->attributesToString($attributes)} >{$value}</datalist>\n";
                break;
            case 'output':
                $elem = "<output {$this->attributesToString($attributes)} >{$value}</output>\n";
                break;
            case 'select':
                $elem = "<select {$this->attributesToString($attributes)} >\n";

                if (is_array($value)) {

                    foreach ($value as $key => $item) {

                        if (is_array($item)) {

                            $elem .= "<optgroup label='{$key}'>\n";

                            foreach ($item as $group) {
                                if (!isset($group['value']) && is_string($group['value'])) continue;
                                if (!isset($group['attributes']) && is_array($group['attributes'])) continue;
                                $elem .= "<option {$this->attributesToString($group['attributes'])}>{$group['value']}</option>\n";
                            }

                            $elem .= "</optgroup>\n";

                        } else {

                            if (!isset($item['value']) && is_string($item['value'])) continue;
                            if (!isset($item['attributes']) && is_array($item['attributes'])) continue;
                            $elem .= "<option {$this->attributesToString($item['attributes'])}>{$item['value']}</option>\n";

                        }
                    }
                }

                $elem .= "</select>\n";
                break;
            default:
                break;
        }

        return $elem;
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function attributesToString(array $attributes): string
    {
        $attr = '';
        foreach ($attributes as $key => $val)
            $attr .= "{$key}='{$val}' ";
        return $attr;
    }

    /**
     * @return mixed|null
     */
    private function getCsrfToken() {
        return csrf_token();
    }

    /**
     * @return mixed|null
     */
    private function getTrackToken() {
        return track_token();
    }

}