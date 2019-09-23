<?php

use Illuminate\Routing\Router;

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

/* @var Router $router */

$router->group(['prefix' => 'user'], function ($router) {

    $router->get('/invite/{email}/{code}',              'UserController@checkActivationCode');
    $router->get('/invite/{email}/{code}/{digitCode}',  'UserController@checkDigitCode');

    $router->post('/invite',                            'UserController@inviteUser');
    $router->post('/reinvite',                          'UserController@reinviteUser');
    $router->post('/firebaseToken',                     'UserController@firebaseToken');
    $router->post('/logout',                            'UserController@logout');
    $router->post('/welcomeMsg',                        'UserController@welcomeMsg');

    $router->put('/invite',                             'UserController@setPasswordFromActivationCode');


    $router->group(['middleware' => 'checkUserToken'], function($router) {
        $router->group(['middleware' => 'school'], function($router) {
            // Role routes
            $router->group(['prefix' => 'role'], function($router) {
                $router->get('/',                           'UserController@getRoles');
            });

            // User routes
            $router->group(['middleware' => 'role:student|leader'], function ($router) {
                $router->get('/dashboardDetails',           'UserController@dashboardDetails');
            });
            $router->group(['middleware' => 'role:school_admin'], function ($router) {
                $router->get('/all',                        'DatatablesController@listAllUsers');
                $router->post('/updatePending',             'UserController@updatePending');
                $router->delete('/{user_id}',               'UserController@delete');
            });
            
            $router->group(['middleware' => 'role:school_admin|leader'], function ($router) {
                $router->get('/light',                      'DatatablesController@light');
            });

            $router->group(['middleware' => 'role:school_admin|leader|student'], function ($router) {  
                $router->put('/{user_id}',                  'UserController@update');
            });
            $router->get('/{user_id}',                      'UserController@show');
            $router->post('/exists',                        'UserController@exists');
            $router->put('/{user_id}/activate',             'UserController@activate');

            $router->delete('/{user_id}/deactivate',        'UserController@deactivate');

        });
    });
});