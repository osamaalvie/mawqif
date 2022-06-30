<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//login route
$router->post('auth/login', 'UserController@authenticate');

//api endpoints
$router->group(['prefix' => 'api'], function () use ($router) {

    //authenticated routes
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('products/', 'ProductController@index');
        $router->post('products/', 'ProductController@store');
        $router->post('products/{id}', 'ProductController@details');
        $router->delete('products/{id}', 'ProductController@destroy');
    });

    //guest and authenticated routes
    $router->get('cart/{id}', 'CartController@index');
    $router->post('cart/', 'CartController@store');
    $router->delete('cart/{id}', 'CartController@destroy');
    $router->put('cart/{id}', 'CartController@update');

});
