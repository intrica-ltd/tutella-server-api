<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$router->group(['prefix' => 'groups', 'middleware' => 'checkToken'], function ($router) {
    $router->post('/store',                         'GroupsController@store');
    $router->post('/update',                        'GroupsController@update');
    $router->post('/delete',                        'GroupsController@delete');
    $router->post('/getUsers',                      'GroupsController@getUsers');
    $router->post('/{user_id}/add',                 'GroupsController@addUserToGroups');
    $router->post('/{user_id}/removeMember',        'GroupsController@removeMember');
    
    $router->get('/list',                           'GroupsController@list');
    $router->get('/getGroupData',                   'GroupsController@getGroupData');
    $router->get('/{id}',                           'GroupsController@show');
    $router->get('/{user_id}/dashboardDetails',     'GroupsController@dashboardDetails');
    $router->get('/{school_id}/groupUsers',         'GroupsController@groupUsers');
    $router->get('/{school_id}/total',              'GroupsController@total');
    $router->get('/{user_id}/show',                 'GroupsController@showForUser');
    $router->get('/{user_id}/getForUser',           'GroupsController@getForUser');
});