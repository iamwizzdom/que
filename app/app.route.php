<?php

use que\route\Route;
use que\route\structure\RouteEntry;
use que\route\structure\RouteImplementEnum;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('/');
    $entry->setRequireCSRFAuth(false);
    $entry->setRequireLogIn(true);
    $entry->setModule('View');
    $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
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