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
$router->group(['prefix' => 'surveys', 'middleware' => 'checkUserToken'], function($router) {
    $router->group(['middleware' => 'school'], function($router) {
        $router->get('/{id}/show',              ['as' => 'survey.show',               'uses' => 'SurveysController@show']);
        $router->get('/{id}/info',              ['as' => 'survey.info',               'uses' => 'SurveysController@info']);
        $router->get('/{id}/answers',           ['as' => 'survey.answers',            'uses' => 'SurveysController@answers']);
        $router->get('/{id}/questions',         ['as' => 'survey.questions',          'uses' => 'SurveysController@questions']);
        $router->get('/list',                   ['as' => 'survey.list',               'uses' => 'SurveysController@list']);
        $router->get('/mySurveys',              ['as' => 'survey.showForUser',        'uses' => 'SurveysController@showForUser']);
        $router->get('/nextSurvey',             ['as' => 'survey.nextSurvey',         'uses' => 'SurveysController@nextSurvey']);

        $router->post('/{id}/cancel',           ['as' => 'survey.cancelSurvey',       'uses' => 'SurveysController@cancelSurvey']);
        $router->post('/{id}/saveAnswers',      ['as' => 'survey.saveAnswers',        'uses' => 'SurveysController@saveAnswers']);
        $router->post('/{id}/copy',             ['as' => 'survey.copy',               'uses' => 'SurveysController@copy']);
        $router->post('/store',                 ['as' => 'survey.store',              'uses' => 'SurveysController@store']);
        $router->post('/removeParticipant',     ['as' => 'survey.removeParticipant',  'uses' => 'SurveysController@removeParticipant']);
        $router->post('/update',                ['as' => 'survey.update',             'uses' => 'SurveysController@update']);

        $router->delete('/{id}/question',       ['as' => 'survey.deleteQuestion',     'uses' => 'SurveysController@deleteQuestion']);
        $router->delete('/{id}',                ['as' => 'survey.delete',             'uses' => 'SurveysController@delete']);
    });
});