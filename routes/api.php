<?php

use App\Http\JsonApi;
use Illuminate\Http\Response;

$router->group(['prefix' => 'v1'], function () use ($router) {

    $router->post('/token', 'AuthenticateController@authenticate');
    $router->put('/token', 'AuthenticateController@validateToken');
    $router->delete('/token', 'AuthenticateController@logout');

    $router->post('/images', ['middleware' => 'auth', 'uses' => 'FileController@uploadImages']);

    $router->get('/{resourceUrlId}/{resourceId}/relationships/{relationship}', 'ResourceController@showResourceIdentifiers');
    $router->get('/{resourceUrlId}/{resourceId}/{relatedResource}', 'ResourceController@showRelatedResource');
    $router->get('/{resourceUrlId}/{resourceId}', 'ResourceController@show');
    $router->get('/{resourceUrlId}', 'ResourceController@index');
    $router->post('/{resourceUrlId}', 'ResourceController@store');
    $router->delete('/{resourceUrlId}/{resourceId}', 'ResourceController@delete');
    $router->put('/{resourceUrlId}/{resourceId}', 'ResourceController@update');
});

$router->get('/{route:.*}', function (Response $response, JsonApi $jsonApi) {
    return $jsonApi->respondResourceNotFound($response);
});
