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
     * This property defines all possible file extensions
     * @var array
     */
    private static array $suffix = [
        '.php',
        '.class.php',
        ".abstract.php",
        ".trait.php",
        ".interface.php",
        ".exception.php"
    ];

    /**
     * This property defines the project root directory from
     * which CacheAutoload will start scanning.
     * You could also set it as an array of directories
     * @var mixed
     */
    private static $root_dir = AUTOLOAD_PATH;

    /**
     * This property defines an array of folder
     * names CacheAutoload must not scan or pick files from
     * @var array
     */
    private static array $exclude = AUTOLOAD_EXCLUDE;

    /**
     * This property defines an array of file paths which
     * CacheAutoload must require each time the server is hit
     * @var array
     */
    private static array $require = AUTOLOAD_REQUIRE;

    /**
     * This property defines the path to CacheAutoload's cache file,
     * where CacheAutoload stores a cache of all file paths
     * @var string
     */
    private static string $cache_file_path = CACHE_PATH . "/autoload.json";

    /**
     * This property defines the project package name
     * @var string
     */
    private static string $package_name = APP_PACKAGE_NAME;

    /**
     * This property is used to determine when a file is found
     * so as to stop searching for an already found file
     * @var bool
     */
    private static bool $found_file = false;

    /**
     * This method adds more file paths to the $cache
     * @param string $key
     * @param string $value
     */
    private static function setCache(string $key, string $value){
        $packageName = self::$package_name;
        $_SESSION['autoload'][$packageName][sha1("{$key}:{$packageName}")] = $value;
        self::storeCache();
    }

    /**
     * This method returns an array of all previously loaded file paths
     * @return array|mixed
     */
    private static function getCache()
    {

        if (isset($_SESSION['autoload'][self::$package_name]) &&
            is_array($_SESSION['autoload'][self::$package_name]))
            return $_SESSION['autoload'][self::$package_name];

        $_SESSION['autoload'] = [];

        if (is_file(self::$cache_file_path)) {
            if (($cache_json_file = @file_get_contents(self::$cache_file_path)) === false) die("Unable to read autoload cache file!");
            elseif (!empty($cache_json_file)) $_SESSION['autoload'] = json_decode($cache_json_file, true);
        }

        return $_SESSION['autoload'][self::$package_name] ??= [];
    }

    /**
     * This method is the reason why this autoloader is called CacheAutoload.
     * It caches all previously loaded file paths
     */
    private static function storeCache()
    {
        if (!is_dir($dir = pathinfo(self::$cache_file_path, PATHINFO_DIRNAME))) mkdir($dir, 0777, true);
        @file_put_contents(self::$cache_file_path, json_encode($_SESSION['autoload'], JSON_PRETTY_PRINT)) or die("Unable to write to autoload cache file!");
    }

    /**
     * This is where everything begins.
     * This method must be ran to initiate CacheAutoload
     */
    public static function init()
    {

        foreach (self::$require as $file) if (is_file($file)) require "{$file}";

        spl_autoload_register(function ($class_name) {

            $packageName = self::$package_name;

            $hash = sha1("{$class_name}:{$packageName}");

            $cache = self::getCache();

            self::$found_file = false;

            if (!isset($cache[$hash])) {
                self::findFile(self::$root_dir, $class_name, self::$exclude);
                return true;
            }

            $file = $cache[$hash];

            if (self::has_exclude($file, self::$exclude)) {
                unset($_SESSION['autoload'][self::$package_name][$hash]);
                self::storeCache();
                return false;
            } elseif (is_file($file)) require_once "$file";
            else self::findFile(self::$root_dir, $class_name, self::$exclude);

            return true;
        });

    }

    /**
     * This method finds and requires files not already cached by CacheAutoload
     * @param $dir
     * @param $class_name
     * @param array $exclude
     * @param bool $scanAllFiles
     */
    private static function findFile($dir, $class_name, $exclude = [], $scanAllFiles = false)
    {
        if (self::$found_file) return;

        if (!is_array($dir)) $dir = [$dir];

        foreach ($dir as $path) {

            if (self::$found_file) break;

            $path = self::resolve_dir_separator($path);

            if (self::has_exclude($path, $exclude)) continue;

            if ($scanAllFiles) {

                $files = self::get_all_php_files($path);
                foreach ($files as $filePath) {
                    if (self::$found_file) break;
                    if (self::scanFile($filePath, $class_name) === true) {
                        self::setCache($class_name, $filePath);
                        require_once "$filePath";
                        self::$found_file = true;
                        break;
                    }
                }

            } else {

                $file_name = explode("/", self::resolve_dir_separator($class_name));
                $file_name = end($file_name) ?: current($file_name);

                $filePath = ""; $count = 0; $suffix_size = count(self::$suffix);

                while (empty($filePath) && $count < $suffix_size) {
                    if (self::$found_file) break;
                    $file = "{$path}/{$file_name}" . self::$suffix[$count];
                    if (is_file($file)) $filePath = $file; $count++;
                }

                if (!self::$found_file && !empty($filePath)) {
                    if (self::scanFile($filePath, $class_name) === true) {
                        self::setCache($class_name, $filePath);
                        require_once "$filePath";
                        self::$found_file = true;
                        break;
                    }
                }
            }

            if (!self::$found_file && !$scanAllFiles) self::findFile($path, $class_name, $exclude, true);
            elseif (!self::$found_file && $scanAllFiles) {
                $dirs = glob("{$path}/*", GLOB_ONLYDIR);
                if (!empty($dirs)) self::findFile($dirs, $class_name, $exclude);
            }

        }

    }

    /**
     * This method scans a files to make sure it's the actual file needed
     * @param string $file_path
     * @param string $class_name
     * @return bool
     */
    private static function scanFile(string $file_path, string $class_name)
    {
        $namespace = self::getNamespace($file_path);
        $classNames = self::getClassName($file_path);
        $match = false;
        foreach ($classNames as $className) {
            if ($namespace !== false) {
                if ($class_name == "{$namespace}\\{$className}") {
                    $match = true;
                    break;
                }
            } else {
                if ($class_name == $className) {
                    $match = true;
                    break;
                }
            }
        }
        return $match;
    }

    /**
     * This method returns the defined namespace in a file if any
     * @param $file_path
     * @return bool|string
     */
    private static function getNamespace($file_path)
    {
        $content = file_get_contents($file_path);
        $tokens = token_get_all($content);
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
     * @param $file_path
     * @return array
     */
    private static function getClassName($file_path)
    {
        $content = file_get_contents($file_path);
        $tokens = token_get_all($content);
        $count = count($tokens);
        $classes = [];
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
        if (!is_dir($dir)) return [];
        $files = scandir($dir) ?: [];
        foreach ($files as $key => $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) != "php" ||
                !is_file("{$dir}/{$file}")) unset($files[$key]);
            else $files[$key] = "{$dir}/{$file}";
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

        foreach ($exclude as $excluded_path) {

            if (strpos($excluded_path, "\\") !== false ||
                strpos($excluded_path, "/") !== false) {

                $excluded_path = self::resolve_dir_separator($excluded_path);

                if (stripos($path, $excluded_path) !== false) return true;

            } else {
                $path_arr = explode("/", $path);
                if (in_array($excluded_path, $path_arr)) return true;
            }

        }
        return false;
    }

    /**
     * @param $dir
     * @return string|string[]|null
     */
    private static function resolve_dir_separator($dir) {

        $dir = preg_replace("/[\\\]/", "/", $dir);
        return preg_replace("/\/\//", "/", $dir);
    }

}

CacheAutoload::init();