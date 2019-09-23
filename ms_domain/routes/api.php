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

$router->group(['prefix' => 'payment', 'middleware' => 'checkUserToken'], function ($router) {
    $router->post('/setup','PaymentController@setupPayment');
    $router->post('/execute','PaymentController@executePayment');
});

$router->post('/contactUs','DashboardController@contactUs');
$router->get('/billing-plan-return','PaymentController@billingAgreementCallback');
$router->get('/billing-plan','PaymentController@billingAgreementCancel');

$router->group(['prefix' => 'notifications', 'middleware' => 'checkUserToken'], function ($router) {
    $router->post('/sendAnnouncement', 'NotificationsController@sendAnnouncement');
    
    $router->get('/', 'NotificationsController@index');
    $router->get('/markAsRead', 'NotificationsController@markAsRead');
});