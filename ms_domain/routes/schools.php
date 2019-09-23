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

$router->group(['prefix' => 'schools', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:school_admin'], function ($router) {
            $router->post('/store',                     ['as' => 'schools.store',                       'uses' => 'SchoolsController@store']);
            $router->post('/update',                    ['as' => 'schools.update',                      'uses' => 'SchoolsController@update']);
            $router->post('/facebookPage/save',         ['as' => 'schools.facebookPageSave',            'uses' => 'SocialController@facebookPageSave']);
            $router->post('/facebookPage',              ['as' => 'schools.facebookPage',                'uses' => 'SocialController@facebookPage']);
	        $router->post('/cancelSubscription',        ['as' => 'schools.cancelSubscription',          'uses' => 'SchoolsController@cancelSubscription']);
            $router->get('/socialConnections',          ['as' => 'schools.socialConnections',           'uses' => 'SchoolsController@socialConnections']);
        });

        $router->group(['middleware' => 'role:super_admin|school_admin'], function ($router) {
            $router->post('/activate',                  ['as' => 'schools.activate',                    'uses' => 'SchoolsController@activate']);
            $router->post('/deactivate',                ['as' => 'schools.deactivate',                  'uses' => 'SchoolsController@deactivate']);

            $router->get('/list',                       ['as' => 'schools.list',                        'uses' => 'SchoolsController@list']);
            $router->get('/view/{id}',                  ['as' => 'schools.view',                        'uses' => 'SchoolsController@view']);
        });
        
        $router->group(['middleware' => 'role:school_admin|leader|student'], function ($router) {
            $router->get('/{id}',                       ['as' => 'schools.school',                      'uses' => 'SchoolsController@show']);
        });
    });
    $router->post('/reactivateSubscription',    ['as' => 'schools.reactivateSubscription',      'uses' => 'SchoolsController@reactivateSubscription']);
});