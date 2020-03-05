<?php


namespace que\template;


use Exception;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigEngine
{
    /**
     * @var
     */
    private static $instance;

    /**
     * @var
     */
    private $twig;

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
     * @return TwigEngine
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

        if (!isset($this->twig))
            $this->twig = new Environment(new FilesystemLoader($this->getTmpDir()), [
                'cache' => "{$this->getCacheDir()}/compile_dir",
            ]);

        try {
            $this->twig->display($this->getTmpFileName(), $this->getContext());
        } catch (Exception $e) {
            throw new QueRuntimeException($e->getMessage(), "Que Templating Error", E_USER_ERROR,
                0, PreviousException::getInstance(1));
        }
    }
}