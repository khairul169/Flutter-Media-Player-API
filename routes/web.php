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

$router->get('/', function () {
    return response('CloudMediaPlayer API');
});

/**
 * Media Route
 */
$router->group(['prefix' => 'media'], function () use ($router) {
    $router->get('/', 'MediaController@index');
    $router->post('/upload', 'MediaController@upload');
    $router->post('/update/{id}', 'MediaController@update');
    $router->post('/delete/{id}', 'MediaController@delete');
});
