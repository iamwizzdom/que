<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/12/2017
 * Time: 2:09 AM
 */
namespace que\template;

use que\common\exception\QueRuntimeException;
use que\common\validate\Track;
use que\error\RuntimeError;
use que\route\Route;
use que\security\CSRF;
use que\session\Session;


class Composer
{
    /**
     * @var Composer
     */
    private static $instance;

    /**
     * @var string
     */
    private $tmpDir = (APP_PATH . "/template/");

    /**
     * @var string
     */
    private $tmpFileName = '';

    /**
     * @var Menu
     */
    private static $menu;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $alert = [];

    /**
     * @var array
     */
    private $form = [];

    private $tmp_module_suffix = [
        '.js',
        '.min.js',
        '.css',
        '.min.css',
        '.scss',
    ];

    /**
     * @var array
     */
    private $css =[];

    /**
     * @var array
     */
    private $script =[];

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Composer
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param array $data
     */
    public function data(array $data){
        $this->data = $data;
    }

    /**
     * @param array $data
     */
    public function dataOverwrite(array $data){
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param array $data
     */
    public function dataExtra(array $data){
        $this->data = array_merge_recursive($this->data, $data);
    }

    /**
     * @param null $key
     * @return array
     */
    public function getData($key = null): array
    {
        return !is_null($key) && isset($this->data[$key]) ? $this->data[$key] : $this->data;
    }

    /**
     * @param array $header
     */
    public function header(array $header){
        $this->header = $header;
    }

    /**
     * @param array $header
     */
    public function headerOverwrite(array $header){
        $this->header = array_merge($this->header, $header);
    }

    /**
     * @param array $header
     */
    public function headerExtra(array $header){
        $this->header = array_merge_recursive($this->header, $header);
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param $type
     * @param $title
     * @param $message
     * @return AlertButton
     */
    public function alert($type, $title, $message): AlertButton {

        if ($type !== ALERT_SUCCESS && $type !== ALERT_ERROR && $type !== ALERT_WARNING)
            throw new QueRuntimeException("You passed an invalid alert type", 'Composer error', E_USER_ERROR);

        if ($type === ALERT_SUCCESS) {
            $this->alert['success'] = [
                'title' => $title,
                'message' => $message
            ];
        }

        if ($type === ALERT_WARNING) {
            $this->alert['warning'] = [
                'title' => $title,
                'message' => $message
            ];
        }

        if ($type === ALERT_ERROR) {
            $this->alert['error'] = [
                'title' => $title,
                'message' => $message
            ];
        }

        return new AlertButton($this, key($this->alert));
    }

    /**
     * @param array $alert
     */
    public function setAlert(array $alert) {
        $this->alert = $alert;
    }

    /**
     * @return array
     */
    public function getAlert(): array
    {
        return $this->alert;
    }

    /**
     * @param string $key
     * @param $data
     */
    public function form(string $key, $data) {
        $this->form[$key] = $data;
    }

    /**
     * @param null $key
     * @return array
     */
    public function getForm($key = null): array
    {
        return !is_null($key) && isset($this->form[$key]) ? $this->form[$key] : $this->form;
    }

    /**
     * @param array $css
     */
    public function css(array $css = []) {
        $this->css = $css;
    }

    /**
     * @return array
     */
    public function getCss(): array
    {
        return $this->css;
    }

    /**
     * @param array $script
     */
    public function script(array $script = []) {
        $this->script = $script;
    }

    /**
     * @return array
     */
    public function getScript(): array
    {
        return $this->script;
    }

    /**
     * @param $tmpFileName
     */
    public function setTmpFileName($tmpFileName){
        $this->tmpFileName = $tmpFileName;
    }

    /**
     * @return string
     */
    public function getTmpFileName(): string
    {
        return $this->tmpFileName;
    }

    public function resetTmpDir(string $dir) {
        $this->tmpDir = $dir;
    }

    private function http_header() {
        $http_header = http()->redirect()->getHeader(); $header = [];
        foreach ($http_header as $key => $value)
            $header['http'][$key] = $value;
        $this->headerExtra($header);
        unset(Session::getInstance()->getFiles()->_get()['http']['http-header']);
    }

    private function http_data() {
        $http_data = http()->redirect()->getData(); $data = [];
        foreach ($http_data as $key => $value) $data['http'][$key] = $value;
        $this->dataExtra($data);
        unset(Session::getInstance()->getFiles()->_get()['http']['http-data']);
    }

    /**
     * @return string|string[]|null
     */
    private function get_tmp_module() {
        $route = Route::getCurrentRoute();
        if (empty($route)) return "";
        return $route->getUri() == '/' ? 'home' : preg_replace("[/]", "-",
            preg_replace('/\{(.*?)\}/', '-', $route->getUri()));
    }

    /**
     * @return array
     */
    private function get_tmp_module_files(): array {
        $files = [
            'js' => [],
            'css' => [],
        ]; $module = $this->get_tmp_module();
        foreach ($this->tmp_module_suffix as $suffix) {
            $js = "js/module/{$module}{$suffix}";
            $css = "css/module/{$module}{$suffix}";
            if (file_exists($this->tmpDir . $js)) $files['js'][] = $js;
            elseif (file_exists($this->tmpDir . $css)) $files['css'][] = $css;
        }
        return $files;
    }

    /**
     * @param null $key
     * @return array
     */
    public function getContext($key = null): array
    {
        return !is_null($key) && isset($this->context[$key]) ? $this->context[$key] : $this->context;
    }

    /**
     * @param $key
     * @param $value
     */
    private function setContext($key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param bool $ignoreDefaultConfig
     * @return $this
     */
    public function prepare(bool $ignoreDefaultConfig = false) {

        $this->http_header();
        $this->http_data();

        $tmpHeader = APP_TEMP_HEADER;

        if (!isset(self::$menu)) self::$menu = new Menu();

        $route = Route::getCurrentRoute();

        $tmpHeader['app_title'] = ((!empty($route) && !empty($route->getTitle())) ?
            $route->getTitle() : $tmpHeader['app_title']);

        $css = (!$ignoreDefaultConfig ? array_merge(APP_TEMP_CSS, $this->getCss()) : $this->getCss());
        $script = (!$ignoreDefaultConfig ? array_merge(APP_TEMP_SCRIPT, $this->getScript()) : $this->getScript());

        $module_files = $this->get_tmp_module_files();
        $script = array_merge($script, $module_files['js']);
        $css = array_merge($css, $module_files['css']);

        array_callback($script, function ($value) {
            return base_url((!str_starts_with($value, 'template') ? "template/{$value}" : $value));
        });

        array_callback($css, function ($value) {
            return base_url((!str_starts_with($value, 'template') ? "template/{$value}" : $value));
        });

        $this->css($css);
        $this->script($script);
        $this->form('track', Track::generateToken());
        $this->form('csrf', (CSRF === true ? CSRF::getInstance()->getToken() : ""));
        $this->header((!$ignoreDefaultConfig ? array_merge($tmpHeader, $this->getHeader()) : $this->getHeader()));

        $this->setContext("script", $this->getScript());
        $this->setContext("css", $this->getCss());
        $this->setContext("data", $this->getData());
        $this->setContext("alert", $this->getAlert());
        $this->setContext("form", $this->getForm());
        $this->setContext("menu", self::$menu->getMenu());
        $this->setContext('header', $this->getHeader());
        $this->setContext("pagination", Pagination::getInstance()->getPaginationFlat());

        return $this;
    }

    /**
     * This will render your template using the smarty templating engine
     * @param bool $returnAsString
     * @return false|string
     */
    public function renderWithSmarty(bool $returnAsString = false) {
        $smarty = SmartyEngine::getInstance();
        $smarty->setTmpDir($this->tmpDir);
        $smarty->setCacheDir((APP_ROOT_PATH . "/cache/tmp/smarty"));
        $smarty->setTmpFileName($this->getTmpFileName());
        $smarty->setContext($this->getContext());

        if ($returnAsString === true) {
            ob_start();
            $smarty->render();
            $content = ob_get_contents();
            if (ob_get_length()) ob_end_clean();
            return $content;
        }

        $smarty->render();
    }

    /**
     * This will render your template using the twig templating engine
     * @param bool $returnAsString
     * @return false|string
     */
    public function renderWithTwig(bool $returnAsString = false) {
        $twig = TwigEngine::getInstance();
        $twig->setTmpDir($this->tmpDir);
        $twig->setCacheDir((APP_ROOT_PATH . "/cache/tmp/twig"));
        $twig->setTmpFileName($this->getTmpFileName());
        $twig->setContext($this->getContext());

        if ($returnAsString === true) {
            ob_start();
            $twig->render();
            $content = ob_get_contents();
            if (ob_get_length()) ob_end_clean();
            return $content;
        }

        $twig->render();
    }

    /**
     * This method simply resets all data passed to composer
     */
    public function _flush() {
        $this->data = $this->header = $this->script = $this->css = $this->form = $this->alert = $this->context = [];
    }

}