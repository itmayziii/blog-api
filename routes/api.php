<?php

$app->group(['prefix' => 'v1'], function () use ($app) {

//    $app->group(['prefix' => 'blogs'], function () use ($app) {
//
//        $app->get('', 'BlogController@index');
//
//    });

    $app->group(['prefix' => 'contact'], function () use ($app) {

        $app->post('', 'ContactMeController@store');
        $app->get('', function () {
            return 'test';
        });

    });

});
