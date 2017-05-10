<?php

$app->group(['prefix' => 'v1'], function () use ($app) {

//    $app->group(['prefix' => 'blogs'], function () use ($app) {
//
//        $app->get('', 'BlogController@index');
//
//    });

    $app->group(['prefix' => 'contacts'], function () use ($app) {

        $app->post('', 'ContactController@store');
        $app->get('/{id}', ['middleware' => 'auth', 'uses' => 'ContactController@show']);
        $app->get('', ['middleware' => 'auth', 'uses' => 'ContactController@index']);

    });

    $app->group(['prefix' => 'blogs'], function () use ($app) {

        $app->post('', ['middleware' => 'auth', 'uses' => 'BlogController@store']);
    });

});