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

$router->group(['prefix' => 'payments', 'middleware' => 'checkUserToken'], function ($router) {
    $router->group(['middleware' => 'school'], function($router) {  
        $router->group(['middleware' => 'role:super_admin|school_admin'], function ($router) {
            $router->get('/invoice/{id}',               ['as' => 'payments.invoice',            'uses' => 'PaymentController@invoice']);
            $router->get('/invoicePdf/{id}',            ['as' => 'payments.invoicePdf',         'uses' => 'PaymentController@invoicePdf']);
        });
        $router->group(['middleware' => 'role:super_admin'], function ($router) {
            $router->post('/markPaid/{id}',             ['as' => 'payments.markPaid',           'uses' => 'PaymentController@markPaid']);
            $router->post('/reject/{id}',               ['as' => 'payments.reject',             'uses' => 'PaymentController@reject']);
        });
        $router->get('/billingAgreement',               ['as' => 'payments.billing_agreement',  'uses' => 'PaymentController@billingAgreement']);
    });
    $router->get('/info',                               ['as' => 'payments.info',               'uses' => 'PaymentController@info']);
    $router->post('/upload',                            ['as' => 'payments.upload',             'uses' => 'PaymentController@upload']);
    $router->get('/download/{id}',                      ['as' => 'payments.download',           'uses' => 'PaymentController@download']);
});