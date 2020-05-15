<?php

use que\route\Route;
use que\route\RouteEntry;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('/');
    $entry->setRequireCSRFAuth(false);
    $entry->setModule(View::class);
});

Route::register()->api(function (RouteEntry $entry) {
    $entry->setUri('api');
    $entry->setRequireCSRFAuth(false);
    $entry->setModule(App::class);
});

Route::init();