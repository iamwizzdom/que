<?php

use que\route\Route;
use que\route\RouteEntry;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->allowPatchRequest()->allowGetRequest();
    $entry->setUri('/');
    $entry->forbidCSRF();
//    $entry->setMiddleware("user.auth");
    $entry->setModule(View::class);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setName('file-upload');
    $entry->setUri('/file-upload');
    $entry->forbidCSRF();
    $entry->setModule(Upload::class);
});

Route::register()->api(function (RouteEntry $entry) {
    $entry->allowPostRequest()->allowPutRequest()->allowGetRequest();
    $entry->setUri('api');
    $entry->forbidCSRF();
    $entry->setModule(App::class);
    $entry->requireLogin(true);
//    $entry->setMiddleware('user.auth');
});

Route::init();
