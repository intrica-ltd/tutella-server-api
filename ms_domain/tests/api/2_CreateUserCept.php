<?php 
$faker = \Faker\Factory::create();

$check_test_admin = \DB::connection('mysql_test_auth')->table('users')->where('email','codeceptionsdomainowner@devsy.com')->first();
if(empty($check_test_admin))
{
    $add_admin = 
    [
        'first_name' => $faker->firstName, 
        'last_name' => $faker->lastName,
        'email'=> 'codeceptionsdomainowner@devsy.com',
        'role' => 'admin',        
    ];
    $url = ENV("OAUTH_URL_API")."user/invite";
    $response      = App\Helpers\CurlHelper::curlPost($url,$add_admin);
    $admin_activation_hash = $response->data->activation_hash;

    $activate_admin = 
    [
        'activation_hash' => $admin_activation_hash,
        'email'=> 'codeceptionsdomainowner@devsy.com',
        'password' => 'Devsy@123',
        'password_confirmation' => 'Devsy@123',    
    ];
    $url = ENV("OAUTH_URL_API")."user/invite";
    $response      = App\Helpers\CurlHelper::curlPut($url,$activate_admin);
}

// Login admin
$I = new ApiTester($scenario);
$I->wantTo('Login admin with API and get Bearer token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Accept', 'application/json');
$I->sendPOST('/login', ["email"=>"codeceptionsdomainowner@devsy.com","password"=>"Devsy@123"]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['token_type' => 'Bearer']);
$access_token = $I->grabDataFromResponseByJsonPath('$..access_token');


// Invite user
$I->wantTo('Create test admin');
$I->amBearerAuthenticated($access_token[0],'json');
$I->sendPOST('/user/invite', ['first_name' => $faker->firstName, 'last_name' => $faker->lastName,'email'=> 'codeceptionsuperadmin@testcodeception.com','role'=>'admin']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);

$check_owner = \DB::connection('mysql_test_auth')->table('users')->where('email','codeceptionsuperadmin@testcodeception.com')->first();
$activation_hash = $check_owner->activation_hash;

// Check activation code and email
$I->wantTo('Check validation of email hash for  admin');
$I->sendGET('/user/invite/codeceptionsuperadmin@testcodeception.com/'.$activation_hash);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);

// Activate created user
$I->wantTo('Validate admin');
$I->amBearerAuthenticated($access_token[0],'json');
$I->sendPUT('/user/invite',['email' => 'codeceptionsuperadmin@testcodeception.com', 'password' => 'Devsy@123','password_confirmation' => 'Devsy@123','activation_hash'=>$activation_hash]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['success' => 1]);