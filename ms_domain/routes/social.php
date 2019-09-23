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
$router->group(['prefix' => 'facebook'], function($router) {
    $router->post('/login',                 ['as' => 'facebook.login',              'uses' => 'SocialController@login']);
    $router->post('/setEnrollmentCode',     ['as' => 'facebook.enrollmentCode',     'uses' => 'SocialController@enrollmentCode']);
});

$router->group(['prefix' => 'instagram'], function($router) {
    $router->post('/login',                 ['as' => 'instagram.login',             'uses' => 'SocialController@instagramLogin']);
    $router->post('/setEnrollmentCode',     ['as' => 'facebook.enrollmentCode',     'uses' => 'SocialController@enrollmentCode']);
    $router->post('/validate',              ['as' => 'instagram.validate',           'uses' => 'SocialController@validateEmail']);
});