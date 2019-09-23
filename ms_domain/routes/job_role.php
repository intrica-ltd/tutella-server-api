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

$router->group(['prefix' => 'jobRole', 'middleware' => 'checkUserToken'], function ($router) {

    $router->get('/', ['as' => 'index', 'uses' => 'JobRolesController@index']);

});