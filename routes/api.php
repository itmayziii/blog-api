<?php

use App\Http\JsonApi;
use Illuminate\Http\Response;

$router->group(['prefix' => 'v1'], function () use ($router) {

    $router->post('/token', 'AuthenticateController@authenticate');
    $router->put('/token', 'AuthenticateController@validateToken');
    $router->delete('/token', 'AuthenticateController@logout');

//    $router->group(['prefix' => 'tags'], function () use ($router) {
//
//        $router->get('', 'TagController@index');
//        $router->get('/{id}', 'TagController@show');
//        $router->post('', ['middleware' => 'auth', 'uses' => 'TagController@store']);
//        $router->put('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@update']);
//        $router->delete('/{id}', ['middleware' => 'auth', 'uses' => 'TagController@delete']);
//
//    });

    $router->post('/images', ['middleware' => 'auth', 'uses' => 'FileController@uploadImages']);

    $router->get('/{resourceUrlId}/{route:.*}', 'ResourceController@show');
    $router->get('/{resourceUrlId}', 'ResourceController@index');
    $router->post('/{resourceUrlId}', 'ResourceController@store');
    $router->delete('/{resourceUrlId}/{route:.*}', 'ResourceController@delete');
    $router->put('/{resourceUrlId}/{route:.*}', 'ResourceController@update');
});

$router->get('/{route:.*}', function (Response $response, JsonApi $jsonApi) {
    return $jsonApi->respondResourceNotFound($response);
});
