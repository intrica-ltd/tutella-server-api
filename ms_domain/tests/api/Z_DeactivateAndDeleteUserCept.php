<?php 
$I = new ApiTester($scenario);

// Login owner
$I->wantTo('Login admin with API and get Bearer token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->sendPOST('/login', ['email' => 'codeceptionsdomainowner@devsy.com', 'password' => 'Devsy@123']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['token_type' => 'Bearer']);
$access_token = $I->grabDataFromResponseByJsonPath('$..access_token');
$user_id_owner = $I->grabDataFromResponseByJsonPath('$.user_id');
$user_id_owner = end($user_id_owner);


// Login with new user
$I->wantTo('Login created admin with API and get Bearer token');
$I->sendPOST('/login', ['email' => 'codeceptionsuperadmin@testcodeception.com', 'password' => 'Devsy@321']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['token_type' => 'Bearer']);
$user_id = $I->grabDataFromResponseByJsonPath('$.user_id');
$user_id = end($user_id);


// Deactivate user with admin token
$I->wantTo('Admin deactivate created user');
$I->amBearerAuthenticated($access_token[0],'json');
$I->sendDELETE("/user/$user_id/deactivate");
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);

// Login with new user after deactivation
$I->wantTo('Login created user with API and get Bearer token');
$I->sendPOST('/login', ['email' => 'codeceptionsuperadmin@testcodeception.com', 'password' => 'Devsy@123']);
$I->seeResponseCodeIs(463);

// Deactivate user with admin token
$I->wantTo('Admin delete created user');
$I->sendDELETE("/user/$user_id");
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);

// Delete admin with admin token
$I->wantTo('Admin delete admin');
$I->sendDELETE("/user/$user_id_owner");
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);