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
$router->group(['prefix' => 'company', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'role:super_admin'], function ($router) {
        $router->post('/store',       ['as' => 'company.store',     'uses' => 'CompanyDetailsController@store']);
    });
    $router->group(['middleware' => 'role:super_admin|school_admin'], function ($router) {        
        $router->get('/details',      ['as' => 'company.details',   'uses' => 'CompanyDetailsController@details']);
        $router->get('/pdf',          ['as' => 'company.pdf',       'uses' => 'CompanyDetailsController@pdf']);
    });
});