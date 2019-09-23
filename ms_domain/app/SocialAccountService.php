<?php

namespace App;

use Laravel\Socialite\Contracts\User as ProviderUser;
use App\Helpers\CurlHelper;

class SocialAccountService
{
    public function createOrGetUser(ProviderUser $providerUser)
    {

        $name = explode(' ', $providerUser->getName(), 2);
        $save_data = [
            'first_name'    => $name[0],
            'last_name'     => $name[1],
            'email'         => $providerUser->getEmail()
        ];

        $oauth_url  = env('OAUTH_URL_API').'user/facebookUser';
        $response   = CurlHelper::curlPost($oauth_url, $save_data);

        if(isset($response->success)) {

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $user = new User([
                    'user_id'       => $response->data->user->id,
                    'first_name'    => $name[0],
                    'last_name'     => $name[1],
                    'email'         => $providerUser->getEmail(),
                ]);

                $account->user()->associate($user);
                $account->save();

                return response()->json()->setStatusCode(200, 'enter_enrollment_code');
                
            } else {

                if($user->enrollment_code == null)
                    return response()->json()->setStatusCode(200, 'enter_enrollment_code');

                $url = ENV("OAUTH_URL")."oauth/token";
                $data = [
                    "grant_type"    => "password",
                    "client_id"     => ENV('OAUTH_CLIENTID'),
                    "client_secret" => ENV('OAUTH_SECRET'),
                    "username"      => $response->data->user->email,
                    "password"      => decrypt($response->data->user->pass),
                    "scope"         => ""
                ];

                // FINAL RESPONSE
                $response = CurlHelper::curlLogin($url,$data);
                if(!isset($response->token_type)) {
                    $fail = ['error'=>1, 'errors' => ['error_user_login']];
                    return response()->json($fail)->setStatusCode(463, 'error_user_login');
                } else {

                    if($user->role != 'super_admin') {
                        $response->school_id = $user->school_id;
                    }

                    UserToken::createToken($response->access_token, $response_user->data);
                
                    $response->user_id = $response_user->data->user->id;
                    $response->first_name = $user->first_name;
                    $response->last_name = $user->last_name; 
                    $response->image = $user->image;
                    $role = $response_user->data->roles;
                    $response->role = (object)[];
                    $roleName = Role::where('name', end($role))->first();
                    if($roleName) {
                        $response->role->name = $roleName['name'];
                        $response->role->display_name = $roleName['display_name'];
                    }

                    return response()->json($response)->setStatusCode(200, 'success');
                }

            }

        }

    }
}