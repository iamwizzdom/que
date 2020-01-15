<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 7/31/2018
 * Time: 2:35 AM
 * Git: https://github.com/iamwizzdom/cache-autoload
 */

class CacheAutoload
{
    /**
     * This property is an array that holds all previously loaded
     * file paths in runtime
     * @var array
     */
    private static $cache = [];

    /**
     * This property defines all possible file extensions
     * @var array
     */
    private static $suffix = [
        '.php',
        '.class.php',
        ".abstract.php",
        ".trait.php",
        ".interface.php",
        ".exception.php"
    ];

    /**
     * This property defines the project root folder from
     * which CacheAutoload will start scanning
     * @var string
     */
    private static $root_dir = APP_ROOT_PATH;

    /**
     * This property defines an array of folder
     * names CacheAutoload must not scan or pick files from
     * @var array
     */
    private static $exclude = AUTOLOAD_EXCLUDE;

    /**
     * This property defines an array of
     * file paths which
     * CacheAutoload must require each time the server is hit
     * @var array
     */
    private static $require = AUTOLOAD_REQUIRE;

    /**
     * This property defines the path to CacheAutoload's cache
     * file, where CacheAutoload stores a cache of all file paths
     * @var string
     */
    private static $cache_file_path = APP_ROOT_PATH . "/cache/autoload.json";

    /**
     * This method adds more file paths to the $cache
     * @param string $key
     * @param string $value
     */
    private static function setCache(string $key, string $value){
        $_SESSION['autoload'][$key] = $value;
        self::storeCache();
    }

    /**
     * This method returns an array of all previously loaded
     * file paths
     * @return array|mixed
     */
    private static function getCache()
    {

        if (!empty($_SESSION['autoload']) &&
            is_array($_SESSION['autoload'])) return $_SESSION['autoload'];

        $_SESSION['autoload'] = [];

        if (file_exists(self::$cache_file_path)) {
            $cache_file = @fopen(self::$cache_file_path, "r")
            or die("Unable to open cache file!");
            $cache_json_file = @fread($cache_file, filesize(self::$cache_file_path));
            if (!empty($cache_json_file))
                $_SESSION['autoload'] = json_decode($cache_json_file, true);
            @fclose($cache_file);
        }

        return $_SESSION['autoload'];

    }

    /**
     * This method is the reason why this autocache is called CacheAutoload.
     * It caches all previously loaded file paths
     */
    private static function storeCache()
    {
        $cache = @fopen(self::$cache_file_path, "w")
        or die("Unable to open cache file!");
        @fwrite($cache, json_encode(self::getCache(), JSON_PRETTY_PRINT));
        @fclose($cache);
    }

    /**
     * This is where everything begins.
     * This method must be run to initiate CacheAutoload
     */
    public static function init()
    {

        foreach (self::$require as $file) if (is_file($file)) require "$file";

        spl_autoload_register(function ($class_name) {

            self::$cache = self::getCache();

            $hash = hash("SHA1", $class_name);

            if (!isset(self::$cache[$hash])) {
                self::findFile(self::$root_dir, $class_name, self::$exclude);
                return true;
            }

            $file = self::$cache[$hash];
            if (self::has_exclude($file, self::$exclude)) return false;
            if (is_file($file)) require "$file"; else self::findFile(self::$root_dir, $class_name, self::$exclude);

            return true;
        });

    }

    /**
     * This method finds and requires files not already cached
     * by CacheAutoload
     * @param $dir
     * @param $fileName
     * @param array $exclude
     */
    private static function findFile($dir, $fileName, $exclude = [])
    {
        $glob = glob($dir . "/*");

        foreach ($glob as $path) {

            if (self::has_exclude($path, $exclude)) continue;

            if (is_dir($path)) {

                $class = str_replace("\\", "/", $fileName);
                $class = explode("/", $class);
                $filePath = ""; $count = 0; $suffix_size = count(self::$suffix);
                while (empty($filePath) && $count < $suffix_size) {
                    $file = $path . "/" . $class[(count($class) - 1)] . self::$suffix[$count];
                    if (is_file($file)) $filePath = $file; $count++;
                }

                if (!empty($filePath)) {
                    if (self::scanFile($filePath, $fileName) === true) {
                        self::setCache(hash("SHA1", $fileName), $filePath);
                        require "$filePath"; break;
                    } else self::findFile($path, $fileName, $exclude);
                } else self::findFile($path, $fileName, $exclude);

            }
        }

    }

    /**
     * This method scans a files to make sure it's the
     * actual file needed
     * @param string $filePath
     * @param string $class_name
     * @return bool
     */
    private static function scanFile(string $filePath, string $class_name)
    {
        $namespace = self::getNamespace($filePath);
        $className = self::getClassName($filePath);
        if ($namespace !== false)
            if ($class_name == ($namespace . "\\" . $className)) return true; else return false;
        else
            if ($class_name == $className) return true; else return false;
    }

    /**
     * This method return the defined namespace in a file
     * @param $filePath
     * @return bool|string
     */
    private static function getNamespace($filePath)
    {
        $src = file_get_contents($filePath);
        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) return false; else return $namespace;
    }

    /**
     * This method returns the class name of a class file
     * @param $filePath
     * @return mixed|string
     */
    private static function getClassName($filePath)
    {
        $php_code = file_get_contents($filePath);

        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (($tokens[$i - 2][0] == T_CLASS ||
                    $tokens[$i - 2][0] == T_INTERFACE ||
                    $tokens[$i - 2][0] == T_ABSTRACT ||
                    $tokens[$i - 2][0] == T_TRAIT)
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        if (!array_key_exists(0, $classes)) return '';
        return $classes[0];
    }

    /**
     * This method checks for excluded directories
     * @param string $path
     * @param array $exclude
     * @return bool
     */
    private static function has_exclude(string $path, array $exclude): bool {
        $path = str_replace("\\", "/", $path);
        foreach ($exclude as $item) {
            if (strpos($item, "/") !== false ||
                strpos($item, "\\") !== false) {
                $exclude_path = str_replace("\\", "/", $item);
                if (stripos($path, $exclude_path) !== false) return true;
            } else {
                $path_arr = explode("/", $path);
                if (in_array($item, $path_arr)) return true;
            }
        }
        return false;
    }

}

CacheAutoload::init();