<?php 

$faker = \Faker\Factory::create();

// Reset password for super admin
$I = new ApiTester($scenario);
$I->wantTo('Ask for reset password and get email with reset password hash');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->sendPOST('/password/reset', ["email"=>"codeceptionsuperadmin@testcodeception.com"]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => '1']);

$check_test_admin = \DB::connection('mysql_test_auth')->table('users')->where('email','codeceptionsuperadmin@testcodeception.com')->first();
//dd($check_test_admin);
$hash_reset_password = $check_test_admin->reset_password_hash;

// Check reset password has for super admin
$I = new ApiTester($scenario);
$I->wantTo('Check email / reset password hash combination');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->sendGET("/password/reset/codeceptionsuperadmin@testcodeception.com/$hash_reset_password");
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => '1']);

// Store new password for super admin
$I = new ApiTester($scenario);
$I->wantTo('Store new password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->sendPUT("/password/store",["email"=>"codeceptionsuperadmin@testcodeception.com","hash"=>$hash_reset_password,"password"=>"Devsy@321","password_confirmation"=>"Devsy@321"]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => '1']);