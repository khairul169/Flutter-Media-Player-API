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
 * Image dir
 */

$router->get('/images/{filename}', function ($filename) {
    $path = base_path() . "/public/images/$filename";
    return response()->download($path, null, [], false);
});

/**
 * Media Route
 */
$router->group(['prefix' => 'media'], function () use ($router) {
    $router->get('/', 'MediaController@index');
    $router->post('/upload', 'MediaController@upload');
    $router->post('/update/{id}', 'MediaController@update');
    $router->post('/delete/{id}', 'MediaController@delete');
    $router->get('/detail/{id}', 'MediaController@getMedia');
    $router->get('/get/{filename}', 'MediaController@downloadMedia');
    $router->get('/cover/{filename}', 'MediaController@getCoverImage');
});

/**
 * Playlist Route
 */
$router->group(['prefix' => 'playlist'], function () use ($router) {
    $router->get('/', 'PlaylistController@index');
    $router->post('/create', 'PlaylistController@create');
    $router->post('/items/{id}/add', 'PlaylistController@addItem');
    $router->post('/items/{id}/remove', 'PlaylistController@removeItem');
    $router->post('/items/{id}', 'PlaylistController@getItems');
});
