<?php

use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* @var Router $router */
$router->group(['prefix' => 'globalSearch', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:school_admin'], function ($router) {
            $router->get('/{id}',                   ['as' => 'groups.find',         'uses' => 'GlobalSearchController@find']);
            $router->get('/users/{type}',           ['as' => 'groups.users',        'uses' => 'GlobalSearchController@users']);
        });
    });
});