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
     * @var array|string
     */
    private static array|string $root_dir = AUTOLOAD_PATH;

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
     * This property defines the path to CacheAutoload cache file,
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
     * to stop searching for an already found file
     * @var bool
     */
    private static bool $found_file = false;

    /**
     * This method adds more file paths to the $cache
     * @param string $key
     * @param string $value
     */
    private static function setCache(string $key, string $value): void
    {
        $packageName = self::$package_name;
        $_SESSION['autoload'][$packageName][sha1("$packageName:$key")] = $value;
        self::storeCache();
    }

    /**
     * This method returns an array of all previously loaded file paths
     * @return array|mixed
     */
    private static function getCache(): mixed
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
    private static function storeCache(): void
    {
        if (!is_dir($dir = pathinfo(self::$cache_file_path, PATHINFO_DIRNAME))) mkdir($dir, 0777, true);
        @file_put_contents(self::$cache_file_path, json_encode($_SESSION['autoload'], JSON_PRETTY_PRINT)) or die("Unable to write to autoload cache file!");
    }

    /**
     * This is where everything begins.
     * This method must run to initiate CacheAutoload
     */
    public static function init(): void
    {

        foreach (self::$require as $file) if (is_file($file)) require "$file";

        spl_autoload_register(function ($className) {

            $packageName = self::$package_name;

            $hash = sha1("$packageName:$className");

            $cache = self::getCache();

            self::$found_file = false;

            if (!isset($cache[$hash])) {
                self::findFile(self::$root_dir, $className, self::$exclude);
                return true;
            }

            $file = $cache[$hash];

            if (self::has_exclude($file, self::$exclude)) {
                unset($_SESSION['autoload'][self::$package_name][$hash]);
                self::storeCache();
                return false;
            } elseif (is_file($file)) require "$file";
            else self::findFile(self::$root_dir, $className, self::$exclude);

            return true;
        });

    }

    /**
     * This method finds and requires files not already cached by CacheAutoload
     * @param $dir
     * @param string $className
     * @param array $exclude
     * @param bool $scanAllFiles
     */
    private static function findFile($dir, string $className, array $exclude = [], bool $scanAllFiles = false): void
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
                    if (self::scanFile($filePath, $className) === true) {
                        self::setCache($className, $filePath);
                        require_once "$filePath";
                        self::$found_file = true;
                        break;
                    }
                }

            } else {

                $file_name = explode("/", self::resolve_dir_separator($className));
                $file_name = end($file_name) ?: current($file_name);

                $filePath = ""; $count = 0; $suffix_size = count(self::$suffix);

                while (empty($filePath) && $count < $suffix_size) {
                    if (self::$found_file) break;
                    $file = "$path/$file_name" . self::$suffix[$count];
                    if (is_file($file)) $filePath = $file; $count++;
                }

                if (!self::$found_file && !empty($filePath)) {
                    if (self::scanFile($filePath, $className) === true) {
                        self::setCache($className, $filePath);
                        require_once "$filePath";
                        self::$found_file = true;
                        break;
                    }
                }
            }

            if (!self::$found_file && !$scanAllFiles) self::findFile($path, $className, $exclude, true);
            elseif (!self::$found_file && $scanAllFiles) {
                $dirs = glob("$path/*", GLOB_ONLYDIR);
                if (!empty($dirs)) self::findFile($dirs, $className, $exclude);
            }

        }

    }

    /**
     * This method scans a files to make sure it's the actual file needed
     * @param string $filePath
     * @param string $className
     * @return bool
     */
    private static function scanFile(string $filePath, string $className): bool
    {
        $namespace = self::getNamespace($filePath);
        $classNames = self::getClassName($filePath);
        $match = false;
        foreach ($classNames as $class) {
            if ($namespace !== false) {
                if ($className == "$namespace\\$class") {
                    $match = true;
                    break;
                }
            } else {
                if ($className == $class) {
                    $match = true;
                    break;
                }
            }
        }
        return $match;
    }

    /**
     * This method returns the defined namespace in a file if any
     * @param $filePath
     * @return bool|string
     */
    private static function getNamespace($filePath): bool|string
    {
        $content = file_get_contents($filePath);
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
     * @param $filePath
     * @return array
     */
    private static function getClassName($filePath): array
    {
        $content = file_get_contents($filePath);
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
    private static function get_all_php_files($dir): bool|array
    {
        if (!is_dir($dir)) return [];
        $files = scandir($dir) ?: [];
        foreach ($files as $key => $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) != "php" ||
                !is_file("$dir/$file")) unset($files[$key]);
            else $files[$key] = "$dir/$file";
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

            if (str_contains($excluded_path, "\\") ||
                str_contains($excluded_path, "/")) {

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
    private static function resolve_dir_separator($dir): array|string|null
    {
        $dir = preg_replace("/\\\\/", "/", $dir);
        return preg_replace("/\/\//", "/", $dir);
    }

}

CacheAutoload::init();