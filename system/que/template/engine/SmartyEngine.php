<?php


namespace que\template;


use Exception;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use Smarty;

class SmartyEngine
{
    /**
     * @var SmartyEngine
     */
    private static $instance;

    /**
     * @var Smarty
     */
    private $smarty;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var
     */
    private $cacheDir;

    /**
     * @var
     */
    private $tmpFileName;

    /**
     * @var array
     */
    private $context;

    protected function __construct()
    {
        if (!isset($this->smarty))
            $this->smarty = new Smarty();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return SmartyEngine
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getTmpDir(): string
    {
        return $this->tmpDir;
    }

    /**
     * @param string $tmpDir
     */
    public function setTmpDir(string $tmpDir): void
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param mixed $cacheDir
     */
    public function setCacheDir($cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return mixed
     */
    public function getTmpFileName()
    {
        return $this->tmpFileName;
    }

    /**
     * @param mixed $tmpFileName
     */
    public function setTmpFileName($tmpFileName): void
    {
        $this->tmpFileName = $tmpFileName;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function render() {

        foreach ($this->getContext() as $key => $context)
            $this->smarty->assign($key, $context);

        $this->smarty->setTemplateDir($this->getTmpDir());
        $this->smarty->setCompileDir( "{$this->getCacheDir()}/compile_dir/");
        $this->smarty->setConfigDir( "{$this->getCacheDir()}/config_dir/");
        $this->smarty->setCacheDir( "{$this->getCacheDir()}/cache_dir/");
//        $this->smarty->caching = 1;
//        $this->smarty->compile_check = true;
//        $this->smarty->cache_lifetime = 60;

        try {
            $this->smarty->display($this->getTmpFileName());
        } catch (Exception $e) {
            throw new QueRuntimeException($e->getMessage(),
                method_exists($e, 'getTitle') ?
                    (!empty($e->getTitle()) ? $e->getTitle() : "Que Runtime Error") : "Que Templating Error",
                E_USER_ERROR, 0, $e->getPrevious() ?: PreviousException::getInstance(2));
        }
    }
}