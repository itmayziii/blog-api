<?php

use App\Http\JsonApi;
use Illuminate\Http\Response;

$router->group(['prefix' => 'v1'], function () use ($router) {

    $router->post('/authenticate', 'AuthenticateController@authenticate');
    $router->get('/token-validation', 'AuthenticateController@validateToken');
    $router->delete('/logout', 'AuthenticateController@logout');

    $router->group(['prefix' => 'contacts'], function () use ($router) {

        $router->post('', 'ContactController@store');
        $router->get('/{id}', ['middleware' => 'auth', 'uses' => 'ContactController@show']);
        $router->get('', ['middleware' => 'auth', 'uses' => 'ContactController@index']);

    });

    $router->group(['prefix' => 'categories'], function () use ($router) {

        $router->get('', 'CategoryController@index');
        $router->get('/{slug}', 'CategoryController@show');
        $router->post('', ['middleware' => 'auth', 'uses' => 'CategoryController@store']);
        $router->put('/{slug}', ['middleware' => 'auth', 'uses' => 'CategoryController@update']);
        $router->delete('/{slug}', ['middleware' => 'auth', 'uses' => 'CategoryController@delete']);

        $router->get('/{slug}/posts', 'CategoryPostController@show');

    });

    $router->group(['prefix' => 'tags'], function () use ($router) {

        $router->get('', 'TagController@index');
        $router->get('/{id}', 'TagController@show');
        $router->post('', ['middleware' => 'auth', 'uses' => 'TagController@store']);
        $router->put('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@update']);
        $router->delete('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@delete']);

    });

    $router->group(['prefix' => 'users'], function () use ($router) {

        $router->get('/{id}', ['middleware' => 'auth', 'uses' => 'UserController@show']);
        $router->get('', ['middleware' => 'auth', 'uses' => 'UserController@index']);
        $router->put('/{id}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
        $router->post('', 'UserController@store');
        $router->delete('/{id}', ['middleware' => 'auth', 'uses' => 'UserController@index']);

    });

    $router->post('/images', ['middleware' => 'auth', 'uses' => 'FileController@uploadImages']);

    $router->get('/webpages/{route:.*}', 'WebPageController@show');

    $router->get('/{resourceUrlId}/{resourceId}', 'ResourceController@show');
    $router->get('/{resourceUrlId}', 'ResourceController@index');
    $router->post('/{resourceUrlId}', 'ResourceController@store');
    $router->delete('/{resourceUrlId}/{resourceId}', 'ResourceController@delete');
    $router->put('/{resourceUrlId}/{resourceId}', 'ResourceController@update');
});

$router->get('/{route:.*}', function (Response $response, JsonApi $jsonApi) {
    dd('afd');
    return $jsonApi->respondResourceNotFound($response);
});
