<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/24/2020
 * Time: 6:11 PM
 */

namespace que\template;


use ArrayAccess;
use que\route\Route;
use que\support\Arr;

class Form implements ArrayAccess
{
    /**
     * @var Form
     */
    private static ?Form $instance = null;

    /**
     * @var string
     */
    private string $formAction = '';

    /**
     * @var string
     */
    private string $formMethod = '';

    /**
     * @var array
     */
    private array $formData = [];

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
     * @param bool $singleton
     * @return Form
     */
    public static function getInstance(bool $singleton = true): Form
    {
        if (!$singleton) return new self();

        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param $offset
     * @param null $default
     * @return array|string|null
     */
    public function getFormData($offset, $default = null)
    {
        return Arr::get($this->formData, $offset, $default);
    }

    /**
     * @param array $formData
     */
    public function setFormData(array $formData): void
    {
        $this->formData = array_merge_recursive($this->formData, $formData);
    }

    /**
     * @param string $action
     * @param array $attributes
     * @param bool $multipart
     * @return string
     */
    function formOpen(string $action = '#', array $attributes = [], bool $multipart = false)
    {

        if ($action != "#" && !str_contains($action, "://")) $action = base_url($action);

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

        $formClose = '';

        if (strtolower($this->formMethod) != "get") {

            if ($this->formAction == '#') {

                if (($currentRoute = current_route()) && $currentRoute->isRequireCSRFAuth())
                    $formClose .= "<input type='hidden' name='csrf' value='{$this->getCsrfToken()}'/>";

            } elseif (($currentRoute = Route::getRouteEntryFromUri($this->formAction))) {

                if ($currentRoute->isRequireCSRFAuth())
                    $formClose .= "<input type='hidden' name='csrf' value='{$this->getCsrfToken()}'/>";
            }
        }

        $formClose .= "<input type='hidden' name='track' value='{$this->getTrackToken()}'/>";

        return "{$formClose}\n</form>\n";
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

                if (($attributes['type'] ?? '') == 'file') $elem = "<input {$this->attributesToString($attributes)} />\n";
                else $elem = "<input value='{$value}' {$this->attributesToString($attributes)} />\n";

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
     * @param string $name
     * @param null $default
     * @return array|mixed
     */
    public function old(string $name, $default = null)
    {
        return http()->_request()->get($name, $default);
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

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return $this->getFormData($offset, $id = unique_id(16)) !== $id;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->getFormData($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        Arr::set($this->formData, $offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        Arr::unset($this->formData, $offset);
    }
}