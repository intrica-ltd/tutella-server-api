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
$router->group(['prefix' => 'groups', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:school_admin'], function ($router) {
            $router->post('/store',                 ['as' => 'groups.store',        'uses' => 'GroupsController@store']);
            $router->post('/update',                ['as' => 'groups.update',       'uses' => 'GroupsController@update']);
            $router->delete('/{id}',                ['as' => 'groups.delete',       'uses' => 'GroupsController@delete']);

            $router->get('/list',                   ['as' => 'groups.list',         'uses' => 'GroupsController@list']);
        });

        $router->group(['middleware' => 'role:school_admin|leader'], function ($router) {
            $router->get('/list',                   ['as' => 'groups.list',         'uses' => 'GroupsController@list']);
            $router->get('/activeUsers',            ['as' => 'groups.activeUsers',  'uses' => 'DatatablesController@activeUsers']);
        });
        $router->group(['middleware' => 'role:school_admin|leader|student'], function ($router) {
            $router->get('/{id}',                   ['as' => 'groups.groups',       'uses' => 'GroupsController@show']);
            $router->get('/{user_id}/get',          ['as' => 'groups.showForUser',  'uses' => 'GroupsController@showForUser']);
        });
    });
});