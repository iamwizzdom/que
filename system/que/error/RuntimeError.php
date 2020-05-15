<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/2/2019
 * Time: 9:07 PM
 */

namespace que\error;

use que\route\Route;
use que\utility\ImageGenerator;

abstract class RuntimeError
{
    /**
     * @var bool
     */
    private static $hasError = false;

    /**
     * @param $error_level
     * @param $error_message
     * @param string $error_file
     * @param string $error_line
     * @param array $error_context
     * @param string $error_title
     * @param int $http_code
     */
    public static function render($error_level, $error_message, $error_file = "", $error_line = "",
                                  $error_context = [], $error_title = "Que Runtime Error",
                                  int $http_code = HTTP_INTERNAL_SERVER_ERROR) {

        if (self::$hasError === true) return; else self::$hasError = true;

        if (ob_get_contents()) ob_clean();

        if (LIVE or ini_get('display_errors') == "Off") {

            $error = [
                'title' => sprintf("%s Error", config('template.app.name')),
                'message' => $error_level == E_USER_NOTICE ? $error_message :
                    "Something unexpected happened, please contact webmaster.",
                'code' => $http_code,
                'backtrace' => false,
            ];

        } else {

            $error = [
                'title' => $error_title,
                'message' => $error_message,
                'backtrace' => true,
                'level' => $error_level,
                'file' => $error_file,
                'line' => $error_line,
                'code' => $http_code,
                'context' => !empty($error_context) ? $error_context : []
            ];

        }

        if (PHP_SAPI == 'cli') die(debug_print($error, true));

        http()->http_response_code($http_code);

        $route = Route::getCurrentRoute();

        if (!empty($route) && $route->getType() == 'api' ||
            http()->_header()->get('X-Requested-With') == 'XMLHttpRequest') {

            if (!empty($route) && $route->getType() != 'api')
                header("Content-Type: application/json", true);

            $error = array_merge($error, [
                'status' => false,
                'code' => $http_code
            ]);

            echo json_encode($error, JSON_PRETTY_PRINT);

        } elseif (!empty($route) && $route->getType() == 'resource') {

            $image = new ImageGenerator();

            $txt = "Title: {$error['title']}\nMessage: {$error['message']}";

            if (isset($error['file']) && isset($error['line']) && isset($error['code'])) {
                $txt .= "\nFile: {$error['file']}\nLine: {$error['line']}\nCode: {$error['code']}";
            }

            $image->setImageTxt($txt);

            $image->render();

        } else {

            $composer = composer();

            $error['message'] = trim($error['message']);
            $tmpPath = LIVE ? (APP_PATH . "/template/") : (QUE_PATH . "/error/tmp");
            $tmpFIle = LIVE ? config('template.error_tmp_path') : "error.html";
            $isFile = is_file("{$tmpPath}/{$tmpFIle}");
            $composer->resetTmpDir($isFile ? $tmpPath : (QUE_PATH . "/error/tmp"));
            $composer->data($error);
            $composer->setTmpFileName($isFile ? $tmpFIle : "error.html");
            $composer->prepare()->renderWithSmarty();
        }

        die();
    }

}