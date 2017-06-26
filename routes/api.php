<?php

$app->group(['prefix' => 'v1'], function () use ($app) {

    $app->post('/authenticate', 'AuthenticateController@authenticate');

    $app->group(['middleware' => 'json-api'], function () use ($app) {

        $app->group(['prefix' => 'contacts'], function () use ($app) {

            $app->post('', 'ContactController@store');
            $app->get('/{id}', ['middleware' => 'auth', 'uses' => 'ContactController@show']);
            $app->get('', ['middleware' => 'auth', 'uses' => 'ContactController@index']);

        });

        $app->group(['prefix' => 'blogs'], function () use ($app) {

            $app->get('', 'BlogController@index');
            $app->get('/{slug}', 'BlogController@show');
            $app->post('', ['middleware' => 'auth', 'uses' => 'BlogController@store']);
            $app->patch('/{slug}', ['middleware' => 'auth', 'uses' => 'BlogController@update']);
            $app->delete('/{slug}', ['middleware' => 'auth', 'uses' => 'BlogController@delete']);

        });

        $app->group(['prefix' => 'categories'], function () use ($app) {

            $app->get('', 'CategoryController@index');
            $app->get('/{id}', 'CategoryController@show');
            $app->post('', ['middleware' => 'auth', 'uses' => 'CategoryController@store']);
            $app->patch('/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@update']);
            $app->delete('/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@delete']);

            $app->get('/{id}/blogs', 'CategoryBlogController@show');
        });

        $app->group(['prefix' => 'tags'], function () use ($app) {

            $app->get('', 'TagController@index');
            $app->get('/{id}', 'TagController@show');
            $app->post('', ['middleware' => 'auth', 'uses' => 'TagController@store']);
            $app->patch('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@update']);
            $app->delete('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@delete']);

        });

        $app->group(['prefix' => 'users'], function () use ($app) {

            $app->get('', ['middleware' => 'auth', 'uses' => 'UserController@show']);
            $app->get('', ['middleware' => 'auth', 'uses' => 'UserController@index']);
            $app->patch('/{id}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
            $app->post('', 'UserController@store');
            $app->delete('/{id}', ['middleware' => 'auth', 'uses' => 'UserController@index']);

        });

    });


});


// TODO add a catch all route for anything that does not match a defined route.
//$app->addRoute(['GET', 'PUT', 'PATCH', 'POST', 'DELETE'], '', '');