<?php

use que\route\Route;
use que\route\RouteEntry;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('/');
    $entry->setRequireCSRFAuth(false);
    $entry->setMiddleware("user.auth");
    $entry->setModule(View::class);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setName('file-upload');
    $entry->setUri('/file-upload');
    $entry->setRequireCSRFAuth(true);
    $entry->setModule(Upload::class);
});

Route::register()->api(function (RouteEntry $entry) {
    $entry->setUri('api/{id:num}');
    $entry->setRequireCSRFAuth(false);
    $entry->setModule(App::class);
    $entry->setMiddleware('user.auth');
});

Route::init();