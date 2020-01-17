<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/2/2019
 * Time: 9:07 PM
 */

namespace que\error;

use Exception;
use que\route\Route;
use que\utility\ImageGenerator;
use Smarty;

abstract class RuntimeError
{
    /**
     * @var bool
     */
    private static $hasError = false;

    /**
     * @var Smarty
     */
    private static $smarty;

    /**
     * @return Smarty
     */
    private static function getSmarty() {
        if (!isset(self::$smarty))
            self::$smarty = new Smarty();
        return self::$smarty;
    }

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
                                  int $http_code = HTTP_INTERNAL_ERROR_CODE) {

        if (self::$hasError === true) return; else self::$hasError = true;

        if (ob_get_contents()) ob_clean();

        $route = Route::getCurrentRoute();

        if (LIVE or ini_get('display_errors') == "Off") {

            $error = [
                'title' => sprintf("%s Error", APP_NAME),
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
            $smarty = self::getSmarty();

            $error['message'] = nl2br($error['message']);

            $smarty->assign("header", APP_TEMP_HEADER);
            $smarty->assign("data", $error);

            $tmp_dir = QUE_PATH . "/error/tmp";
            $tmp = "error.html";

            if (is_file(APP_ERROR_TMP)) {
                $pathinfo = pathinfo(APP_ERROR_TMP);
                $tmp_dir = $pathinfo["dirname"];
                $tmp = $pathinfo["basename"];
            }

            $smarty->addTemplateDir($tmp_dir);
            $smarty->setCompileDir(APP_ROOT_PATH . "/cache/tmp/smarty/compile_dir/");
            $smarty->setConfigDir(APP_ROOT_PATH . "/cache/tmp/smarty/config_dir/");
            $smarty->setCacheDir(APP_ROOT_PATH . "/cache/tmp/smarty/cache_dir/");
            $smarty->setCacheLifetime(1);

            try {
                $smarty->display($tmp);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }

        die();
    }

}