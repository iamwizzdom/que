<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/27/2020
 * Time: 6:38 PM
 */

register_shutdown_function(function() {
    $error = error_get_last();
    if (!empty($error)) call_user_func(['que\error\RuntimeError', 'render'],
        $error["type"], $error["message"], $error["file"], $error["line"]);
});

set_error_handler(['que\error\RuntimeError', 'render']);

set_exception_handler(function ($e) {
    if (!empty($e)) {
        $_e = $e->getPrevious() ?: $e;
        call_user_func(['que\error\RuntimeError', 'render'],
            $e->getCode(), $e->getMessage(), $_e->getFile(),
            $_e->getLine(), $e->getTrace(), method_exists($e, 'getTitle') ?
                ($e->getTitle() ?: "Que Runtime Error") : "Que Runtime Error");
    }
});
