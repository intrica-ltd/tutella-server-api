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
$router->group(['middleware' => 'checkToken'], function ($router) {
    $router->get('/{id}/show',                      'SurveysController@show');
    $router->get('/{id}/info',                      'SurveysController@info');
    $router->get('/{id}/answers',                   'SurveysController@answers');
    $router->get('/{user_id}/showForUser',          'SurveysController@showForUser');
    $router->get('/{user_id}/getForUser',           'SurveysController@getForUser');
    $router->get('/{user_id}/dashboardDetails',     'SurveysController@dashboardDetails');
    $router->get('/{survey_id}/questions',          'SurveysController@questions');
    $router->get('/{school_id}/lastSurvey',         'SurveysController@lastSurvey');
    $router->get('/list',                           'SurveysController@list');
    $router->get('/nextSurvey',                     'SurveysController@nextSurvey');

    $router->post('/{id}/cancelSurvey',             'SurveysController@cancelSurvey');
    $router->post('/{id}/deleteQuestion',           'SurveysController@deleteQuestion');
    $router->post('/{id}/delete',                   'SurveysController@delete');
    $router->post('/{id}/saveAnswers',              'SurveysController@saveAnswers');
    $router->post('/{id}/copy',                     'SurveysController@copy');
    $router->post('/{user_id}/removeAssignee',      'SurveysController@removeAssignee');
    $router->post('/deletedGroup',                  'SurveysController@deletedGroup');
    $router->post('/updatedGroup',                  'SurveysController@updatedGroup');
    $router->post('/store',                         'SurveysController@store');
    $router->post('/update',                        'SurveysController@update');
    $router->post('/removeParticipant',             'SurveysController@removeParticipant');
});