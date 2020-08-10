<?php

use que\route\Route;
use que\route\RouteEntry;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->allowPatchRequest()->allowGetRequest();
    $entry->setUri('/');
    $entry->requireCSRFAuth();
    $entry->setMiddleware("user.auth");
    $entry->setModule(View::class);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setName('file-upload');
    $entry->setUri('/file-upload');
    $entry->requireCSRFAuth();
    $entry->setModule(Upload::class);
});

Route::register()->api(function (RouteEntry $entry) {
    $entry->allowPostRequest()->allowPutRequest()->allowGetRequest();
    $entry->setUri('api/{id:num}');
    $entry->requireCSRFAuth();
    $entry->setModule(App::class);
    $entry->setMiddleware('user.auth');
});

Route::init();
