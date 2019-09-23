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
$router->group(['prefix' => 'dashboard', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:school_admin'], function ($router) {
            $router->get('/info',       ['as' => 'dashboard.info',      'uses' => 'DashboardController@info']);
        });
    });
});