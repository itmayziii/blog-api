<?php

use App\Http\JsonApi;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

$router->group(['prefix' => 'v1'], function () use ($router) {

    $router->post('/token', 'AuthenticateController@authenticate');
    $router->put('/token', 'AuthenticateController@validateToken');
    $router->delete('/token', 'AuthenticateController@logout');

//    $router->get('/email', function () {
//        $mailer = app()->make('mailer');
//        $mailer->to('tommymay37@gmail.com')->send(new App\Mail\Contact());
////        \Illuminate\Support\Facades\Mail::to('tommymay37@gmail.com')->send(new \App\Mail\Contact());
//        return 'apples';
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
