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
$router->group(['prefix' => 'documents', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->group(['middleware' => 'role:leader|student'], function ($router) {
            $router->get('/myPhotos',                       ['as' => 'documents.myPhotos',              'uses' => 'DocumentsController@myPhotos']);
        });
        
        $router->group(['middleware' => 'role:school_admin|leader|student'], function ($router) {
            $router->post('/upload',                        ['as' => 'documents.upload',                'uses' => 'DocumentsController@upload']);
            $router->post('/download',                      ['as' => 'documents.download',              'uses' => 'DocumentsController@download']);
            
            $router->get('/list',                           ['as' => 'documents.list',                  'uses' => 'DocumentsController@list']);
            $router->get('/socialFeeds',                    ['as' => 'documents.socialFeeds',           'uses' => 'SocialController@socialFeeds']);
            $router->get('/filter',                         ['as' => 'documents.filter',                'uses' => 'DocumentsController@filter']);
        });
        
        $router->get('/profilePhoto/{photo_id}',            ['as' => 'documents.profilePhoto',          'uses' => 'DocumentsController@profilePhoto']);
        $router->get('/thumbnail/{photo_id}',               ['as' => 'documents.getThumbnailPhoto',     'uses' => 'DocumentsController@getThumbnailPhoto']);
        $router->get('/{photo_id}',                         ['as' => 'documents.getPhoto',              'uses' => 'DocumentsController@getPhoto']);

        $router->post('/delete',                            ['as' => 'documents.delete',                'uses' => 'DocumentsController@delete']);
    });
});