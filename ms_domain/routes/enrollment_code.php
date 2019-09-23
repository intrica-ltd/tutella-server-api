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

$router->group(['prefix' => 'enrollmentCode', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:leader|school_admin'], function ($router) {
            $router->post('/generate',              ['as' => 'index',               'uses' => 'EnrollmentCodeController@generate']);
        });
        $router->group(['middleware' => 'role:school_admin'], function ($router) {
            $router->post('/school/generate',       ['as' => 'index',               'uses' => 'EnrollmentCodeController@generateSchool']);
        });  
        $router->group(['middleware' => 'role:leader'], function ($router) {
            $router->get('/last',                   ['as' => 'lastGenerated',       'uses' => 'EnrollmentCodeController@lastGenerated']);
        });  
    });
});