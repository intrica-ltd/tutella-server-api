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

// User - owner invites and login
$router->group([], function($router) {

    $router->post('owner/invite',                   'OwnerController@create');

    $router->group(['middleware' => 'role:school_admin'], function ($router) {
        $router->post('/password/resetForUser',     'LoginController@resetForUser');
    });

    $router->post('/password/reset',                'LoginController@reset');
    $router->post('/password/change',               'LoginController@changePassword');
    $router->put('/password/store',                 'LoginController@storeNewPassword');
    $router->get('/password/reset/{email}/{hash}',  'LoginController@checkNewPasswordHash');
    
    $router->get('/verify_account/{email}/{hash}',  'LoginController@verifyAccount');
    $router->get('/confirm_email/{email}/{hash}',   'LoginController@storeNewEmail');

    $router->post('/login',                         'LoginController@login');
    $router->post('/refreshToken',                  'LoginController@refresh');

    $router->post('/user/validateInfo',             'RegisterController@validateInfo');
    $router->post('/user/registerEnrollmentCode',   'RegisterController@registerEnrollmentCode');
    $router->post('/user/register',                 'RegisterController@register');
    $router->post('/schoolAdmin/register',          'RegisterController@registerSchoolAdmin');

    $router->post('/enrollmentCode/verify',         'EnrollmentCodeController@verify');

    $router->get('/facebook/callback',              'SocialAuthController@callback');
    $router->get('/facebook/redirect',              'SocialAuthController@redirect');
    $router->post('/facebook/enrollmentCode',        'SocialAuthController@enrollmentCode');
});