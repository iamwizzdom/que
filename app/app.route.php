<?php

use que\route\Route;
use que\route\structure\RouteEntry;
use que\route\structure\RouteImplementEnum;

require 'app.settings.php';

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('/');
    $entry->setModule('module\home\View');
    $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('logout');
    $entry->setModule('module\access\Logout');
    $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
    $entry->setRequireLogIn(true);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('login');
    $entry->setModule('module\access\Login');
    $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
});

Route::register()->web(function (RouteEntry $entry) {
    $entry->setUri('signup');
    $entry->setModule('module\access\Signup');
    $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
});

Route::register()->group('profile', function (string $prefix) {

    return [
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('/');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\profile\View');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
        },
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('bank');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\profile\Bank');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
        },
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('message');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\profile\Message');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
        }
    ];

});

Route::register()->group('money', function (string $prefix) {

    return [
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('send');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\money\Send');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
        }
    ];

});

Route::register()->group("loan", function (string $prefix) {

    return [
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('/');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\loan\View');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_PAGE);
        },
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('{loanID:num}/detail');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\loan\Detail');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_INFO);
        },
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('post');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\loan\Post');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_ADD);
        },
        function(RouteEntry $entry) {
            $entry->setType('web');
            $entry->setUri('request');
            $entry->setRequireLogIn(true);
            $entry->setModule('module\loan\Request');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_ADD);
        }
    ];
});

Route::register()->group('api/v1', function (string $prefix) {

    Route::register()->group("{$prefix}/profile", function (string $prefix) {

        return [
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('update/{action:alpha}');
                $entry->setRequireLogIn(true);
                $entry->setModule('module\profile\load\Update');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            },
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('bank/{type:alpha}/{action:alpha}');
                $entry->setRequireLogIn(true);
                $entry->setModule('module\profile\load\Bank');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            },
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('message/{action:alpha}');
                $entry->setRequireLogIn(true);
                $entry->setModule('module\profile\load\Message');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            },
        ];

    });

    Route::register()->group("{$prefix}/loan", function (string $prefix) {

        return [
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('{loanID:num}/update/{action:alpha}');
                $entry->setRequireLogIn(true);
                $entry->setModule('module\loan\load\Update');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            },
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('{loanID:num}/conversation/{action:alpha}');
                $entry->setRequireLogIn(true);
                $entry->setModule('module\loan\load\Conversation');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            }
        ];

    });


    Route::register()->group("{$prefix}/service", function (string $prefix) {

        return [
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('countries');
                $entry->setModule('module\service\load\Countries');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            },
            function (RouteEntry $entry) {
                $entry->setType('api');
                $entry->setUri('states');
                $entry->setRequireCSRFAuth(true);
                $entry->setModule('module\service\load\States');
                $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
            }
        ];

    });

    return [
        function(RouteEntry $entry) {
            $entry->setType('api');
            $entry->setUri('signup');
            $entry->setModule('module\access\load\Signup');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
        },
        function(RouteEntry $entry) {
            $entry->setType('api');
            $entry->setUri('login');
            $entry->setModule('module\access\load\Login');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
        },
        function(RouteEntry $entry) {
            $entry->setType('api');
            $entry->setUri('login');
            $entry->setModule('module\access\load\Login');
            $entry->setImplement(RouteImplementEnum::IMPLEMENT_API);
        }
    ];

});

Route::init();