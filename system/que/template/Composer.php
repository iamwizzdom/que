<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/12/2017
 * Time: 2:09 AM
 */

namespace que\template;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\common\exception\QueRuntimeException;
use que\common\exception\RouteException;
use que\common\validator\Track;
use que\http\HTTP;
use que\route\Route;
use que\security\CSRF;
use que\session\Session;


class Composer
{
    /**
     * @var Composer|null
     */
    private static ?Composer $instance = null;

    /**
     * @var string
     */
    private string $tmpDir = (APP_PATH . "/template/");

    /**
     * @var bool
     */
    private bool $singleton;

    /**
     * @var string
     */
    private string $tmpFileName = '';

    /**
     * @var Menu|null
     */
    private ?Menu $menu = null;

    /**
     * @var Form|null
     */
    private ?Form $form;

    /**
     * @var array
     */
    private array $context = [];

    /**
     * @var array
     */
    private array $misc = [];

    /**
     * @var array
     */
    private array $header = [];

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var array
     */
    private array $alert = [];

    private array $tmp_module_suffix = [
        '.js',
        '.min.js',
        '.css',
        '.min.css',
        '.scss',
    ];

    /**
     * @var array
     */
    private array $css = [];

    /**
     * @var array
     */
    private array $js = [];

    /**
     * @var bool
     */
    private bool $prepared = false;

    protected function __construct(bool $singleton)
    {
        $this->singleton = $singleton;
        $this->form = Form::getInstance($singleton);
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param bool $singleton
     * @return Composer
     */
    public static function getInstance(bool $singleton = true): Composer
    {
        if (!$singleton) return new self($singleton);

        if (!isset(self::$instance))
            self::$instance = new self($singleton);
        return self::$instance;
    }

    /**
     * @param array $misc
     */
    public function misc(array $misc)
    {
        $this->misc = array_merge_recursive($this->misc, $misc);
    }

    /**
     * @return array
     */
    public function getMisc(): array
    {
        return $this->misc;
    }

    /**
     * @param array $data
     */
    public function data(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array $data
     */
    public function dataOverwrite(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param array $data
     */
    public function dataExtra(array $data)
    {
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
    public function header(array $header)
    {
        $this->header = $header;
    }

    /**
     * @param array $header
     */
    public function headerOverwrite(array $header)
    {
        $this->header = array_merge($this->header, $header);
    }

    /**
     * @param array $header
     */
    public function headerExtra(array $header)
    {
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
    public function alert($type, $title, $message): AlertButton
    {

        if ($type !== ALERT_SUCCESS && $type !== ALERT_ERROR && $type !== ALERT_WARNING)
            throw new QueRuntimeException("You passed an invalid alert type", 'Composer error',
                E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

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
    public function setAlert(array $alert)
    {
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
     * @param array $formData
     */
    public function form(array $formData)
    {
        $this->form->addFormData($formData);
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param array $css
     */
    public function css(array $css = [])
    {
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
     * @param array $js
     */
    public function js(array $js = [])
    {
        $this->js = $js;
    }

    /**
     * @return array
     */
    public function getJs(): array
    {
        return $this->js;
    }

    /**
     * @param $tmpFileName
     */
    public function setTmpFileName($tmpFileName)
    {
        $this->tmpFileName = $tmpFileName;
    }

    /**
     * @return string
     */
    public function getTmpFileName(): string
    {
        return $this->tmpFileName;
    }

    /**
     * @return string
     */
    public function getTmpDir(): string
    {
        return $this->tmpDir;
    }

    public function resetTmpDir(string $dir)
    {
        $this->tmpDir = $dir;
    }

    private function http_header()
    {
        $http_header = http()->redirect()->getHeader();
        $header = [];
        foreach ($http_header as $key => $value) $header['http'][$key] = $value;
        $this->headerExtra($header);
        Session::getInstance()->getFiles()->_unset("http.header");
    }

    private function http_data()
    {
        $http_data = http()->redirect()->getData();
        $data = [];
        foreach ($http_data as $key => $value) $data['http'][$key] = $value;
        $this->dataExtra($data);
        Session::getInstance()->getFiles()->_unset("http.data");
    }

    /**
     * @return string|string[]|null
     */
    private function get_tmp_module(): array|string|null
    {
        $route = Route::getCurrentRoute();
        if (empty($route)) return "";
        return $route->getUri() == '/' ? 'home' : preg_replace("[/]", "-",
            preg_replace('/{(.*?)}/', '-', $route->getUri()));
    }

    /**
     * @return array
     */
    #[ArrayShape(['js' => "array", 'css' => "array"])] private function get_tmp_module_files(): array
    {
        $files = [
            'js' => [],
            'css' => [],
        ];
        $module = $this->get_tmp_module();
        foreach ($this->tmp_module_suffix as $suffix) {
            $js = "js/module/{$module}{$suffix}";
            $css = "css/module/{$module}{$suffix}";
            if (file_exists($this->tmpDir . $js)) $files['js'][] = str_start_from($js, 'js/');
            elseif (file_exists($this->tmpDir . $css)) $files['css'][] = str_start_from($css, 'css/');
        }
        return $files;
    }

    /**
     * @param null $key
     * @return array
     */
    #[Pure] public function getContext($key = null): array
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
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->prepared;
    }

    /**
     * @param bool $prepared
     */
    public function setPrepared(bool $prepared): void
    {
        $this->prepared = $prepared;
    }

    /**
     * @param bool $ignoreDefaultCss
     * @param bool $ignoreDefaultScript
     * @param bool $ignoreDefaultHeader
     * @return $this
     * @throws QueException
     */
    public function prepare(bool $ignoreDefaultCss = false, bool $ignoreDefaultScript = false, bool $ignoreDefaultHeader = false): self
    {

        $this->http_header();
        $this->http_data();

        $tmpHeader = config('template.app.header');

        if (empty($this->menu)) $this->menu = new Menu();

        $route = Route::getCurrentRoute();

        $tmpHeader['title'] = ((!empty($route) && !empty($route->getTitle())) ? $route->getTitle() : $tmpHeader['title'] ?? '');
        $tmpHeader['desc'] = ((!empty($route) && !empty($route->getDescription())) ? $route->getDescription() : $tmpHeader['desc'] ?? '');

        $css = (!$ignoreDefaultCss ? array_merge(config('template.app.css', []), $this->getCss()) : $this->getCss());
        $js = (!$ignoreDefaultScript ? array_merge(config('template.app.js', []), $this->getJs()) : $this->getJs());

        $module_files = $this->get_tmp_module_files();
        $js = array_merge($js, $module_files['js']);
        $css = array_merge($css, $module_files['css']);

        array_callback($js, function ($uri) {

            if (str__starts_with($uri, 'http://') ||
                str__starts_with($uri, 'https://')) return $uri;

            if (str__starts_with($uri, '/') ||
                str__starts_with($uri, './') ||
                str__starts_with($uri, '../')) {

                $uri = str_start_from($uri, '/');
                $uri = "template/{$uri}";

            } else $uri = "template/js/{$uri}";

            try {
                return base_url($uri);
            } catch (\Exception $e) {
                throw new QueException("Template preparation failed", "Composer Error", E_ERROR, PreviousException::getInstance(1));
            }
        });

        array_callback($css, function ($uri) {

            if (str__starts_with($uri, 'http://') ||
                str__starts_with($uri, 'https://')) return $uri;

            if (str__starts_with($uri, '/') ||
                str__starts_with($uri, './') ||
                str__starts_with($uri, '../')) {

                $uri = str_start_from($uri, '/');
                $uri = "template/{$uri}";

            } else $uri = "template/css/{$uri}";
            try {
                return base_url($uri);
            } catch (\Exception $e) {
                throw new QueException("Template preparation failed", "Composer Error", E_ERROR, PreviousException::getInstance(1));
            }
        });

        $this->css($css);
        $this->js($js);
        $this->form([
            'track' => Track::generateToken(),
            'csrf' => (config('auth.csrf', false) === true ? CSRF::getInstance()->getToken() : null)
        ]);
        $this->header((!$ignoreDefaultHeader ? array_merge($tmpHeader, $this->getHeader()) : $this->getHeader()));

        $this->setContext("js", $this->getJs());
        $this->setContext("css", $this->getCss());
        $this->setContext("misc", $this->getMisc());
        $this->setContext("data", $this->getData());
        $this->setContext("alert", $this->getAlert());
        $this->setContext("form", $this->getForm());
        $this->setContext("menu", $this->menu->getMenu());
        $this->setContext('header', $this->getHeader());
        $this->setContext("pagination", Pagination::getInstance());
        $this->setPrepared(true);

        return $this;
    }

    /**
     * This will render your template using the smarty templating engine
     * @param bool $returnAsString
     * @return bool|string|null
     */
    public function renderWithSmarty(bool $returnAsString = false): bool|string|null
    {
        if ($returnAsString) ob_start();

        if (!$this->isPrepared()) throw new QueRuntimeException(
            "The current template '{$this->getTmpFileName()}' is not prepared for rending. You cannot render an unprepared template.",
            'Composer error', E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $smarty = SmartyEngine::getInstance();
        $smarty->setTmpDir($this->getTmpDir());
        $smarty->setCacheDir((QUE_PATH . "/cache/tmp/smarty"));
        $smarty->setTmpFileName($this->getTmpFileName());
        $smarty->setContext($this->getContext());

        $smarty->render();
        $this->_flush();

        if ($returnAsString === true) {
            $content = ob_get_contents();
            if (ob_get_length()) ob_end_clean();
            return $content;
        }
        return null;
    }

    /**
     * This will render your template using the twig templating engine
     * @param bool $returnAsString
     * @return bool|string|null
     */
    public function renderWithTwig(bool $returnAsString = false): bool|string|null
    {
        if ($returnAsString) ob_start();

        if (!$this->isPrepared()) throw new QueRuntimeException(
            "The current template '{$this->getTmpFileName()}' is not prepared for rending. You cannot render an unprepared template.",
                'Composer error', E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $twig = TwigEngine::getInstance();
        $twig->setTmpDir($this->getTmpDir());
        $twig->setCacheDir((QUE_PATH . "/cache/tmp/twig"));
        $twig->setTmpFileName($this->getTmpFileName());
        $twig->setContext($this->getContext());

        $twig->render();
        $this->_flush();

        if ($returnAsString === true) {
            $content = ob_get_contents();
            if (ob_get_length()) ob_end_clean();
            return $content;
        }
        return null;
    }

    /**
     * This method simply resets all data passed to composer
     */
    public function _flush()
    {
        $this->data = $this->header = $this->js =
        $this->css = $this->alert = $this->context = [];
        $this->prepared = false;
    }

}