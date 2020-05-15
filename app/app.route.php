<?php

use que\route\Route;
use que\route\RouteEntry;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('/');
    $entry->setRequireCSRFAuth(false);
    $entry->setModule('View');
});

Route::register()->api(function (RouteEntry $entry) {
    $entry->setUri('api');
    $entry->setRequireCSRFAuth(false);
    $entry->setModule('App');
});

//Route::register()->group('profile', function (string $prefix) {
//
//    return [
//        function(RouteEntry $entry) {
//            $entry->setType('web');
//            $entry->setUri('/');
//            $entry->setRequireLogIn(true);
//            $entry->setModule('module\profile\View');
//            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
//        },
//        function(RouteEntry $entry) {
//            $entry->setType('web');
//            $entry->setUri('bank');
//            $entry->setRequireLogIn(true);
//            $entry->setModule('module\profile\Bank');
//            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
//        },
//        function(RouteEntry $entry) {
//            $entry->setType('web');
//            $entry->setUri('message');
//            $entry->setRequireLogIn(true);
//            $entry->setModule('module\profile\Message');
//            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
//        }
//    ];
//
//});

Route::init();