<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 7:49 PM
 */

use que\support\Env;

$path = APP_PATH . '/.env';

if (!LIVE && file_exists(APP_PATH . '/.env.development')) {
    $path = APP_PATH . '/.env.development';
} elseif (LIVE && file_exists(APP_PATH . '/.env.production')) {
    $path = APP_PATH . '/.env.production';
}

$env = new Env($path);
$env->load();
