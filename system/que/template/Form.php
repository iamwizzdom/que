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
    private string $action = '';

    /**
     * @var string
     */
    private string $method = '';

    /**
     * @var array
     */
    private array $data = [];

    /**
     * Form constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
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

    public function hasError($key)
    {
        return Arr::isset($this->data['error'] ?? [], $key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getError($key): mixed
    {
        return $this->data['error'][$key] ?? null;
    }

    /**
     * @param array $error
     */
    public function setError(array $error) {
        $this->data['error'] = $error;
    }

    /**
     * @param array $error
     */
    public function addError(array $error) {
        $this->data['error'] ??= [];
        $this->data['error'] = array_merge($this->data['error'], $error);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getStatus($key): mixed
    {
        return $this->data['status'][$key] ?? SUCCESS;
    }

    /**
     * @param array $status
     */
    public function setStatus(array $status) {
        $this->data['status'] = $status;
    }

    /**
     * @param array $status
     */
    public function addStatus(array $status) {
        $this->data['status'] ??= [];
        $this->data['status'] = array_merge($this->data['status'], $status);
    }

    /**
     * @param $offset
     * @param null $default
     * @return array|string|null
     */
    public function getData($offset, $default = null): array|string|null
    {
        return Arr::get($this->data, $offset, $default);
    }

    /**
     * @param array $formData
     */
    public function setData(array $formData): void
    {
        if (isset($formData['error'])) unset($formData['error']);
        if (isset($formData['status'])) unset($formData['status']);
        $this->data = array_merge($this->data, $formData);
    }

    /**
     * @param array $formData
     */
    public function addData(array $formData): void
    {
        if (isset($formData['error'])) unset($formData['error']);
        if (isset($formData['status'])) unset($formData['status']);
        $this->data = array_merge_recursive($this->data, $formData);
    }

    /**
     * @param string $action
     * @param array $attributes
     * @param bool $multipart
     * @return string
     */
    function formOpen(string $action = '#', array $attributes = [], bool $multipart = false): string
    {

        if ($action != "#" && !str__contains($action, "://")) $action = base_url($action);

        if (!in_array('method', $keys = array_keys($attributes)))
            $attributes['method'] = 'post';

        if ($multipart === true && !in_array('enctype', $keys))
            $attributes['enctype'] = 'multipart/form-data';

        $this->action = $action;
        $this->method = $attributes['method'];

        return "<form action='{$action}' {$this->attributesToString($attributes)}>\n";
    }

    /**
     * @return string
     * @throws \que\common\exception\RouteException
     */
    public function formClose(): string
    {

        $formClose = '';

        if (strtolower($this->method) != "get") {

            if ($this->action == '#') {

                if (($currentRoute = current_route()) && $currentRoute->isForbidCSRF())
                    $formClose .= "<input type='hidden' name='csrf' value='{$this->getCsrfToken()}'/>";

            } elseif (($currentRoute = Route::getRouteEntryFromUri($this->action))) {

                if ($currentRoute->isForbidCSRF())
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
    public function formElement(string $tagName, $value, array $attributes = []): string
    {

        $elem = '';

        switch ($tagName) {
            case 'input':
                $attributes['value'] = $value;
                if (($attributes['type'] ?? '') == 'file') unset($attributes['value']);
                $elem = "<input {$this->attributesToString($attributes)} />\n";
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
     * @return mixed
     */
    public function old(string $name, $default = null): mixed
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
     * @return mixed
     */
    private function getCsrfToken(): mixed
    {
        return csrf_token();
    }

    /**
     * @return mixed
     */
    private function getTrackToken(): mixed
    {
        return track_token();
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return Arr::isset($this->data, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->getData($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        if ('error' != $offset && 'status' != $offset) Arr::set($this->data, $offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        if ('error' != $offset && 'status' != $offset) Arr::unset($this->data, $offset);
    }
}
