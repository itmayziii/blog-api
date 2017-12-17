<?php

$app->group(['prefix' => 'v1'], function () use ($app) {

    $app->get('/authenticate', 'AuthenticateController@authenticate');
    $app->get('/token-validation', 'AuthenticateController@validateToken');

    $app->post('/images', ['middleware' => 'auth', 'uses' => 'FileController@uploadImage']);

    $app->group(['middleware' => 'json-api'], function () use ($app) {

        $app->group(['prefix' => 'contacts'], function () use ($app) {

            $app->post('', 'ContactController@store');
            $app->get('/{id}', ['middleware' => 'auth', 'uses' => 'ContactController@show']);
            $app->get('', ['middleware' => 'auth', 'uses' => 'ContactController@index']);

        });

        $app->group(['prefix' => 'posts'], function () use ($app) {

            $app->get('', 'PostController@index');
            $app->get('/{slug}', 'PostController@show');
            $app->post('', ['middleware' => 'auth', 'uses' => 'PostController@store']);
            $app->patch('/{slug}', ['middleware' => 'auth', 'uses' => 'PostController@update']);
            $app->delete('/{slug}', ['middleware' => 'auth', 'uses' => 'PostController@delete']);

        });

        $app->group(['prefix' => 'categories'], function () use ($app) {

            $app->get('', 'CategoryController@index');
            $app->get('/{id}', 'CategoryController@show');
            $app->post('', ['middleware' => 'auth', 'uses' => 'CategoryController@store']);
            $app->patch('/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@update']);
            $app->delete('/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@delete']);

            $app->get('/{id}/posts', 'CategoryPostController@show');
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