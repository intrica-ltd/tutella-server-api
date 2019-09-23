<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\SocialAccountService;
use App\Models\EnrollmentCode;
use App\Models\User;
use App\Models\UserToken;
use App\Models\Role;
use Socialite;
use App\Helpers\CurlHelper;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();   
    }   

    public function callback(SocialAccountService $service)
    {
        $user = $service->createOrGetUser(Socialite::driver('facebook')->user());
        
        return redirect()->to('/home');
    }

    public function enrollmentCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'enrollment_code' => 'required'
        ]);          
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $check = EnrollmentCode::where('code', $request->get('enrollment_code'))->first();

        if($check && strtotime('now') < strtotime($check->expiary_date)) {
            $enrollmentCodeGenerated = ($check->leader_id != null) ? $check->leader_id : $check->school_admin_id;
            $school = User::where('user_id', $enrollmentCodeGenerated)->first();

            $user = User::where('email', $request->get('email'))->first();
            if($user) {
                $user->school_id = $school->school_id;
                $user->enrollment_code = $request->get('enrollment_code');
                $user->save();

                $save_data['email'] = $request->get('email');

                $oauth_url  = env('OAUTH_URL_API').'user/facebookUser';
                $response   = CurlHelper::curlPost($oauth_url, $save_data);
                
                if(isset($response->success)) {
                    
                    $url = ENV("OAUTH_URL")."oauth/token";
                    $data = [
                        "grant_type"    => "password",
                        "client_id"     => ENV('OAUTH_CLIENTID'),
                        "client_secret" => ENV('OAUTH_SECRET'),
                        "username"      => $response->data->user->email,
                        "password"      => $response->data->user->pass,
                        "scope"         => ""
                    ];
                    // FINAL RESPONSE
                    $response = CurlHelper::curlLogin($url,$data);
                    if(!isset($response->token_type)) {
                        $fail = ['error'=>1, 'errors' => ['error_user_login']];
                        return response()->json($fail)->setStatusCode(463, 'error_user_login');
                    } else {

                        $url = ENV("OAUTH_URL_API")."user/".$request->get('email');
                        $response_user = CurlHelper::curlGet($url);

                        if(!isset($response_user->success)) {
                            $fail = ['error'=>1, 'errors' => ['error_user_login.']];
                            return response()->json($fail)->setStatusCode(463, 'error_user_login');
                        } else {
                            if(isset($response_user->success) && (isset($response_user->data->user->active) && $response_user->data->user->active != 1) ) {
                                $fail = ['error'=>1, 'errors' => ['error_user_not_active']];
                                return response()->json($fail)->setStatusCode(464, 'error_user_not_active');
                            }
                        }

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

                    return $user;
                }
            }
            return response()->json()->setStatusCode(463, 'error_user_not_found');
        }

        return response()->json()->setStatusCode(489, 'error_enrollment_code_expired');
    }
}