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
    private static $cache_file_path = QUE_PATH . "/cache/autoload.json";

    /**
     * This method adds more file paths to the $cache
     * @param string $key
     * @param string $value
     */
    private static function setCache(string $key, string $value){
        $_SESSION['autoload'][self::get_package_name()][$key] = $value;
        self::storeCache();
    }

    /**
     * This method returns an array of all previously loaded
     * file paths
     * @return array|mixed
     */
    private static function getCache()
    {
        $packageName = self::get_package_name();

        if (!empty($_SESSION['autoload'][$packageName]) &&
            is_array($_SESSION['autoload'][$packageName])) return $_SESSION['autoload'][$packageName];

        $_SESSION['autoload'][$packageName] = [];

        if (file_exists(self::$cache_file_path)) {
            if (($cache_json_file = @file_get_contents(self::$cache_file_path)) === false)
                die("Unable to read autoload cache file!");
            if (!empty($cache_json_file))
                $_SESSION['autoload'][$packageName] = json_decode($cache_json_file, true);
        }

        return $_SESSION['autoload'][$packageName];
    }

    /**
     * This method is the reason why this autocache is called CacheAutoload.
     * It caches all previously loaded file paths
     */
    private static function storeCache()
    {
        @file_put_contents(self::$cache_file_path,
            json_encode(self::getCache(), JSON_PRETTY_PRINT))
        or die("Unable to write to autoload cache file!");
    }

    /**
     * This is where everything begins.
     * This method must be run to initiate CacheAutoload
     */
    public static function init()
    {

        foreach (self::$require as $file) if (is_file($file)) require "$file";

        spl_autoload_register(function ($class_name) {

            $hash = sha1($class_name);

            self::$cache = self::getCache();

            if (!isset(self::$cache[$hash])) {
                self::findFile(self::$root_dir, $class_name, self::$exclude);
                return true;
            }

            $file = self::$cache[$hash];
            if (self::has_exclude($file, self::$exclude)) return false;
            elseif (is_file($file)) require_once "$file";
            else self::findFile(self::$root_dir, $class_name, self::$exclude);

            return true;
        });

    }

    /**
     * This method finds and requires files not already cached
     * by CacheAutoload
     * @param $dir
     * @param $fileName
     * @param array $exclude
     * @param bool $scanAllFiles
     */
    private static function findFile($dir, $fileName, $exclude = [], $scanAllFiles = false)
    {
        $glob = glob($dir . "/*");

        $file_name = '';
        if (!$scanAllFiles) {
            $file_name = preg_replace("/\\\\/", "/", $fileName);
            $file_name = explode("/", $file_name);
            $file_name = end($file_name);
        }

        $suffix_size = count(self::$suffix);

        $found = false;

        foreach ($glob as $path) {

            if (self::has_exclude($path, $exclude)) continue;

            if (is_dir($path)) {

                if ($scanAllFiles) {

                    $files = self::get_all_php_files($path);
                    foreach ($files as $file) {
                        $filePath = "{$path}/{$file}";
                        if (self::scanFile($filePath, $fileName) === true) {
                            self::setCache(sha1($fileName), $filePath);
                            require_once "$filePath";
                            $found = true; break;
                        }
                    }

                    if ($found) break;
                    else self::findFile($path, $fileName, $exclude, $scanAllFiles);

                } else {

                    $filePath = ""; $count = 0;
                    while (empty($filePath) && $count < $suffix_size) {
                        $file = $path . "/" . $file_name . self::$suffix[$count];
                        if (is_file($file)) $filePath = $file; $count++;
                    }

                    if (!empty($filePath)) {
                        if (self::scanFile($filePath, $fileName) === true) {
                            self::setCache(sha1($fileName), $filePath);
                            require_once "$filePath";
                            $found = true; break;
                        } else self::findFile($path, $fileName, $exclude);
                    } else self::findFile($path, $fileName, $exclude);

                }

            }
        }

        if (!$found && !$scanAllFiles) self::findFile($dir, $fileName, $exclude, true);

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
        $classNames = self::getClassName($filePath);
        $match = false;
        foreach ($classNames as $className) {
            if ($namespace !== false) {
                if ($class_name === ($namespace . "\\" . $className)) {
                    $match = true;
                    break;
                }
            } else {
                if ($class_name === $className) {
                    $match = true;
                    break;
                }
            }
        }
        return $match;
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
        return !$namespace_ok ? false : $namespace;
    }

    /**
     * This method returns the class name(s) found in a class file
     * @param $filePath
     * @return array
     */
    private static function getClassName($filePath)
    {
        $php_code = file_get_contents($filePath);

        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (($tokens[$i - 2][0] === T_CLASS ||
                    $tokens[$i - 2][0] === T_INTERFACE ||
                    $tokens[$i - 2][0] === T_ABSTRACT ||
                    $tokens[$i - 2][0] === T_TRAIT)
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) $classes[] = $tokens[$i][1];
        }
        return $classes;
    }

    /**
     * This method returns a list of php files in a given directory
     * @param $dir
     * @return array|false
     */
    private static function get_all_php_files($dir) {
        $files = scandir($dir);
        foreach ($files as $key => $file) {
            if (!str_ends_with(strtolower($file), '.php'))
                unset($files[$key]);
        }
        return $files;
    }

    /**
     * This method checks for excluded directories
     * @param string $path
     * @param array $exclude
     * @return bool
     */
    private static function has_exclude(string $path, array $exclude): bool {
        $path = preg_replace("/\\\\/", "/", $path);
        foreach ($exclude as $item) {
            if (strpos($item, "/") !== false ||
                strpos($item, "\\") !== false) {
                $exclude_path = preg_replace("/\\\\/", "/", $item);
                if (stripos($path, $exclude_path) !== false) return true;
            } else {
                $path_arr = explode("/", $path);
                if (in_array($item, $path_arr)) return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    private static function get_package_name() {
        $package_path_arr = explode("/", preg_replace(
            "/\\\\/", "/", self::$root_dir));
        return end($package_path_arr) ?: current($package_path_arr);
    }

}

CacheAutoload::init();