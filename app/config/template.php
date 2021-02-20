<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/21/2020
 * Time: 11:08 AM
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | Here are each of the template configuration setup for your application.
    |
    */

    'app' => [
        'header' => [
            'title' => '',
            'name' => '',
            'desc' => '',
            'fav_icon' => '',
            'icon' => '',
            'logo' => [
                'small' => [
                    'white' => '',
                    'dark' => '',
                    'origin' => ''
                ],
                'large' => [
                    'white' => '',
                    'dark' => '',
                    'origin' => ''
                ]
            ],

            /*
             | Allowed SEO robots
             */
            'robots' => 'index, follow',

            /*
             | SEO keywords separated by comma
             */
            'keywords' => 'que, awesome, framework, php',
            'domain' => APP_HOST,
        ],

        /*
         | Path to your default css files
         */
        'css' => [

        ],

        /*
         | Path to your default js files
         */
        'js' => [

        ]
    ],

    /*
     | This page will be used as your custom page for outputting errors,
     | the page will receive a 'Title', a 'Message' and a 'Code'
     | ----------------------------------------------------------
     | You are to capture the above data as follows
     | --------------------------------------------
     | Title: $data.title
     | Message: $data.message
     | Code: $data.code -- [HTTP response code]
     */
    'error_tmp_path' => ''
];