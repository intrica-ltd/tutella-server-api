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
$router->group(['prefix' => 'schools', 'middleware' => 'checkToken'], function ($router) {
    $router->post('/store',                     'SchoolsController@store');
    $router->post('/activate',                  'SchoolsController@activate');
    $router->post('/deactivate',                'SchoolsController@deactivate');
    $router->post('/update',                    'SchoolsController@update');
	$router->post('/facebookPage',              'SchoolsController@facebookPage');
	$router->post('/enrollmentCode',            'SchoolsController@enrollmentCode');
	$router->post('/{id}/changeLogo',           'SchoolsController@changeLogo');

	$router->delete('/{id}/destroy',            'SchoolsController@destroy');
    
    $router->get('/list',                       'SchoolsController@list');
    $router->get('/{id}/getFbPage',             'SchoolsController@getFbPage');
    $router->get('/{id}/socialConnections',     'SchoolsController@socialConnections');
    $router->get('/{id}',                       'SchoolsController@show');
});