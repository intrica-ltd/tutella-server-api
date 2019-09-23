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

//  AUTH CLIENT

Route::post('/user/storeInvitedUser',                           'UserController@storeInvitedUser')->middleware('auth_client');
Route::post('/user/store',                                      'UserController@store')->middleware('auth_client');
Route::post('/user/validateInfo',                               'UserController@validateInfo')->middleware('auth_client');
Route::post('/user/inviteUser',                                 'UserController@inviteUser')->middleware('auth_client');
Route::post('/user/facebookUser',                               'UserController@facebookUser')->middleware('auth_client');
Route::post('/user/instaUser',                                  'UserController@instaUser')->middleware('auth_client');
Route::post('/{user_id}/removeUser',                            'UserController@removeUser')->middleware('auth_client');
Route::put('/user/invite',                                      'UserController@edit')->middleware('auth_client');
Route::get('/user/invite/{id}/{activation_hash}',               'UserController@showFromActivationHash')->middleware('auth_client');
Route::get('/user/invite/{id}/{activation_hash}/{digitCode}',   'UserController@showFromActivationCode')->middleware('auth_client');
Route::get('/user/{id}',                                        'UserController@show')->middleware('auth_client');
Route::put('/user/{id}',                                        'UserController@update')->middleware('auth_client');
Route::get('/statistic/user',                                   'UserController@userStatistics')->middleware('auth_client');
Route::delete('/user/{id}',                                     'UserController@destroy')->middleware('auth_client');
Route::delete('/user/{id}/delete',                              'UserController@delete')->middleware('auth_client');
Route::delete('/user/{id}/deactivate',                          'UserController@deactivate')->middleware('auth_client');
Route::put('/user/{id}/activate',                               'UserController@activate')->middleware('auth_client');
Route::post('/user/newemail/store',                             'UserController@storeNewEmail')->middleware('auth_client');
Route::put('/user/restore/{email}',                             'UserController@restore')->middleware('auth_client');
Route::put('/user/create',                                      'UserController@restore')->middleware('auth_client');

Route::post('/user/password/resetForUser',                      'Auth\ResetPasswordController@resetForUser')->middleware('auth_client');
Route::post('/user/password/reset',                             'Auth\ResetPasswordController@askResetPassword')->middleware('auth_client');
Route::post('/user/password/change',                            'Auth\ResetPasswordController@changePassword')->middleware('auth_client');
Route::put('/user/password/reset',                              'Auth\ResetPasswordController@storeNewPassword')->middleware('auth_client');
Route::get('/user/password/reset/{email}/{hash}',               'Auth\ResetPasswordController@checkHashForResetPassword')->middleware('auth_client');

Route::get('/user/verify_account/{email}/{hash}',               'UserController@verifyAccount')->middleware('auth_client');
Route::get('user/token/authService',                            'UserController@authService')->middleware('auth_client');

// AUTH USER
//Route::get('/user', 'UserController@index');
Route::get('user/token/checkMyAccessToken',                     'UserController@checkMyAccessToken')->middleware('auth:api');