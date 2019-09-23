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
    $router->group(['prefix' => 'payment'], function ($router) {
        $router->post('/setup',                             'PaymentController@setupPayment');
        $router->post('/execute',                           'PaymentController@executePayment');
    });

    $router->group(['prefix' => 'billing'], function ($router) {
        $router->get('/list',                               'BillingController@listAllPackages');
        $router->get('/list/{school_id}',                   'BillingController@listSchoolPackage');    
        $router->post('/store/{school_id}/{package_id}',    'BillingController@store');
    });

    $router->post('/saveBillingAgreement',                  'InfoController@saveBillingAgreement');
    $router->post('/billingAgreement',                      'InfoController@billingAgreement');
    $router->post('/suspendAgreement',                      'InfoController@suspendAgreement');
    $router->get('/schools/info',                           'InfoController@schoolsInfo');
    $router->get('/school/info/{school_id}',                'InfoController@schoolInfoId');
    $router->get('/school/{school_id}',                     'InfoController@schoolInfo');
    $router->get('/getUnpaidInvoices/{school_id}',          'InfoController@getUnpaidInvoices');
    $router->get('/invoice/{id}',                           'InfoController@invoice');
    $router->get('/download/{id}',                          'InfoController@download');
    $router->get('/{school_id}/overdue',                    'InfoController@overdue');
    $router->get('/{school_id}/totalSchoolOverdue',         'InfoController@totalSchoolOverdue');

    $router->post('/markPaid/{id}',                         'InfoController@markPaid');
    $router->post('/reject/{id}',                           'InfoController@reject');
    $router->post('/upload',                                'InfoController@upload');

    $router->get('/monthlyStatistics',                      'BillingStatisticsController@monthlyStatistics');
});