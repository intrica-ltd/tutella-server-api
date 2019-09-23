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

$router->group(['prefix' => 'documents', 'middleware' => 'checkToken'], function ($router) {
    $router->post('/documentsDetails',              'DocumentsController@documentsDetails');
    $router->post('/documentGroups',                'DocumentsController@documentGroups');
    $router->post('/updateSchool',                  'DocumentsController@updateSchool');
    $router->post('/updateOwnerName',               'DocumentsController@updateOwnerName');
    $router->post('/store',                         'DocumentsController@store');
    $router->post('/delete',                        'DocumentsController@delete');
    $router->post('/facebookFeed',                  'DocumentsController@facebookFeed');

    $router->get('/{school_id}/list',               'DocumentsController@list');
    $router->get('/{school_id}/totalDocuments',     'DocumentsController@totalDocuments');
    $router->get('/{school_id}/socialFeed',         'DocumentsController@getSocialFeed');
    $router->get('/{user_id}/myPhotos',             'DocumentsController@myPhotos');
    $router->get('/{user_id}/dashboardDetails',     'DocumentsController@dashboardDetails');
    $router->get('/{doc_id}',                       'DocumentsController@show');
});
