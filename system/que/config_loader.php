<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 7:49 PM
 */

use que\support\Config;

$config = new Config();

$files = scandir($dir = APP_PATH . "/config") ?: [];

foreach ($files as $key => $file) {
    if (strtolower(pathinfo($file, PATHINFO_FILENAME)) == 'config' ||
        strtolower(pathinfo($file, PATHINFO_EXTENSION)) != "php" && !is_file($file))
        unset($files[$key]);
    else $files[$key] = "{$dir}/{$file}";
}

$config->load($files);
