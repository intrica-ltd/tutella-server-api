<?php 

$client_data = \DB::table('z_testcodeception')->where('type','client_access_token')->select('value')->first();
$client_access_token = json_decode($client_data->value);
$client_access_token = $client_access_token->access_token;
$faker = \Faker\Factory::create();

$I = new ApiTester($scenario);
$I->wantTo('Create test users');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->amBearerAuthenticated($client_access_token,'json');

$roles = \DB::table('roles')->get();

foreach($roles as $role)
{
    // Invite  user
    $I->sendPOST('/user/invite', ['first_name' => $faker->firstName, 'last_name' => $faker->lastName,'email'=> 'codeception'.$role->name.'@testcodeception.com','role'=>$role->name]);
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    $check_owner = \DB::table('users')->where('email',"codeception$role->name@testcodeception.com")->first();

    $activation_hash = $check_owner->activation_hash;
    $activation_code = $check_owner->activation_code;
    $user_id         = $check_owner->id;
    
    // Check activation code and email 
    $I->wantTo('Check validation of email hash for user');
    $I->sendGET("/user/invite/codeception$role->name@testcodeception.com/$activation_hash");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    // Check activation code and digit
    $I->wantTo('Check validation of email hash for user');
    $I->sendGET("/user/invite/codeception$role->name@testcodeception.com/$activation_hash/$activation_code");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    // Activate created user
    $I->wantTo('Validate user');
    $I->sendPUT('/user/invite',['email' => "codeception$role->name@testcodeception.com", 'password' => 'Devsy@123','password_confirmation' => 'Devsy@123','activation_hash'=>$activation_hash,'digit_code'=>$activation_code]);
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    // Get data for user
    $I->wantTo('Get data for user');
    $I->sendGET("/user/codeception$role->name@testcodeception.com");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    // Delete user
    $I->wantTo('Delete  user');
    $I->sendDELETE("/user/$user_id");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);
/*
    // Restore user
    $I->wantTo('Restore  user');
    $I->sendPUT("/user/restore/codeception$role->name@testcodeception.com");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);

    // Permanently delete user
    $I->wantTo('Permanently delete user');
    $I->sendDELETE("/user/$user_id/delete");
    $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
    $I->seeResponseIsJson();
    $I->seeResponseContainsJson(['success' => 1]);
*/
}
