<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CurlHelper;
use App\Models\User;
use App\Models\Role;
use App\Models\UserToken;
use App\Http\Requests\Users\ChangePasswordForUserRequest;
use App\Http\Requests\Users\ChangePasswordRequest;
use App\Http\Controllers\EnrollmentCodeController;
use App\Models\EnrollmentCode;

/** @SWG\Swagger(
 *     host="domain.tutella.local/api/",
 *     schemes={"http"},
 *     @SWG\Info(
 *         version="1.0",
 *         title="Domain API",
 *         @SWG\Contact(name="Devsy", url="http://www.devsy.com"),
 *     ),
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 * )
 */
class LoginController extends Controller
{

    protected $enrollmentCode;
    public function __construct(EnrollmentCodeController $enrollmentCode)
    {
       $this->enrollmentCode = $enrollmentCode;
    }
    
    /**
     * @SWG\Post(path="/login",
     *   summary="Login user to system",
     *   description="Login user to system ",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   tags={"Login"},
      *   @SWG\Parameter(
      *     in="body",
      *     name="email",
      *     description="email",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="password",
      *     description="password",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> user_data]")
     * )
     */

    public function login(Request $req)
    {
        
        $validator = \Validator::make($req->all(), [
            'email'      => 'required',
            'password'  => 'required',
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(463, 'error_user_login');
        }

        // CHECK IF USER ACTIVE
        $url = ENV("OAUTH_URL_API")."user/".$req->get('email');
        $response_user = CurlHelper::curlGet($url);

        if(!isset($response_user->success)) {
            $fail = ['error'=>1, 'errors' => ['error_user_login']];
            return response()->json($fail)->setStatusCode(463, 'error_user_login');
        } else {
            if(isset($response_user->success) && (isset($response_user->data->user->active) && $response_user->data->user->active != 1) ) {
                $fail = ['error'=>1, 'errors' => ['error_user_not_active']];
                return response()->json($fail)->setStatusCode(464, 'error_user_not_active');
            }
        }

        $user = User::where('username', $response_user->data->user->username)->first();

        $school_data = [];
        if($user->school_id != null) {
            $url_schools = env('SCHOOLS_URL_API').'schools/'.$user->school_id;
            $response_school = CurlHelper::curlGet($url_schools);
            if(isset($response_school->success)) {
                if($response_school->data->school->active == 0) {
                    if($user->role == 'leader' || $user->role == 'student')
                        return response()->json()->setStatusCode(458, 'error_user_not_active'); 
                }
                if($user->role == 'school_admin') {
                    $school_data['school_name'] = $response_school->data->school->school_name;
                    $school_data['logo_id'] = $response_school->data->school->logo_id;
                    $school_data['enrollment_code'] = $response_school->data->school->enrollment_code;
                    $school_data['poster_id'] = $response_school->data->school->poster_id;

                } else if($user->role == 'leader' || $user->role == 'student') {
                    $school_data['school_name'] = $response_school->data->school->school_name;
                    $school_data['address'] = $response_school->data->school->address;
                    $school_data['email'] = $response_school->data->school->email;
                    $school_data['phone'] = $response_school->data->school->phone;
                    $school_data['logo_id'] = $response_school->data->school->logo_id;
                }
            }
        }

        if($user->role == 'leader') {
            $code = EnrollmentCode::where('leader_id', $user->user_id)->orderBy('id', 'desc')->first();

            if($code) {
                $code_data['code'] = $code->code;
                if(time() < strtotime($code->expiary_date))
                    $code_data['expired'] = 0;
                else
                    $code_data['expired'] = 1;

                $code_data['expiary_date'] = $code->expiary_date;
            } else
                $code_data = ['code' => ''];
        }

        // LOGIN
        $url = ENV("OAUTH_URL")."oauth/token";
        $data = 
        [
            "grant_type" => "password",
            "client_id" => ENV('OAUTH_CLIENTID'),
            "client_secret" => ENV('OAUTH_SECRET'),
            "username" => $response_user->data->user->username,
            "password" => $req->get('password'),
            "scope" => ""
        ];

        // FINAL RESPONSE
        $response = CurlHelper::curlLogin($url,$data);
        if(!isset($response->token_type)) {
            $fail = ['error'=>1, 'errors' => ['error_user_login']];
            return response()->json($fail)->setStatusCode(463, 'error_user_login');
        } else {
            if($user) {
                User::updateUserData($response_user->data->user->id);
            } else {
                $user       = User::createUserData($response_user->data);
            }

            if($user->role != 'super_admin') {
                $response->school_id = $user->school_id;
                if(isset($response_school->success))
                    $response->school_active = $response_school->data->school->active;
            }

            UserToken::createToken($response->access_token, $response_user->data);
        
            $response->user_id = $response_user->data->user->id;
            $response->first_name = $user->first_name;
            $response->last_name = $user->last_name; 
            $response->username = $user->username;
            $response->email = $user->email;
            $response->phone = $user->phone;
            $response->image = $user->image;
            $response->image_id = $user->image_id;
            $response->welcome_msg = $user->welcome_msg;
            $role = $response_user->data->roles;
            $response->fb_user = $user->fb_user;
            $response->insta_user = $user->insta_user;
            $response->role = (object)[];
            $roleName = Role::where('name', end($role))->first();
            $response->school_data = $school_data;
            if($user->role == 'leader')
                $response->code_data = $code_data;
            
            if($roleName) {
                $response->role->name = $roleName['name'];
                $response->role->display_name = $roleName['display_name'];
            }

            return response()->json($response)->setStatusCode(200, 'success');
        }
    }

    /**
     * @SWG\Post(path="/password/reset",
     *   summary="Ask for password reset and send EMAIL with reset pasword hash",
     *   description="Ask for password reset and send EMAIL with reset pasword hash",
     *   produces={"application/json"},
     *   tags={"Reset password"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="client",
     *     description="The client sending the request [values: web, mobile]",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> []]")
    * )
    */
    public function reset(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email'      => 'required|email',
            'client'     => 'required'
        ]);          
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $user_social = User::where('email', $request->get('email'))->first();

        if($user_social && $user_social->fb_user == 0 && $user_social->insta_user == 0) {
            $url      = ENV("OAUTH_URL_API")."user/".$request->get('email');
            $response = CurlHelper::curlGet($url);
            
            if(isset($response->success)) {
                $role_user = end($response->data->roles);
                $user = $response->data->user;
                $oauth_url     = env('OAUTH_URL_API').'user/password/reset';
                $response = CurlHelper::curlPost($oauth_url,['email' => $request->get('email')]);

                if(isset($response->data->hash)) {
                    $email_data['name']   = $user->first_name.' '.$user->last_name;
                    $email_data['hash']   = $response->data->hash;

                    $email_data['email'] = $request->get('email');

                    if($role_user == 'school_admin' || $role_user == 'super_admin') 
                        $email_data['link'] = env('TUTELLA_WEB').'/reset-password-finish/'.$email_data['email']."/".$email_data['hash'];
                    else if($role_user == 'leader')
                        $email_data['link'] = env('TUTELLA_WEB').'/reset-password-finish-mobile-leader/'.$email_data['email']."/".$email_data['hash'];
                    else
                        $email_data['link'] = env('TUTELLA_WEB').'/reset-password-finish-mobile-student/'.$email_data['email']."/".$email_data['hash'];
                    
                    \Mail::send('emails.resetPassword', $email_data, function ($m) use ($email_data) {
                        $m->from('no-reply@tutella.com', '');
                        $m->to($email_data['email'], '')->subject('Reset password');
                    });
                    
                }           
            } 
        }
        return response()->json(['success'=>1]); 
    }

    /**
     * @SWG\Put(path="/password/store",
     *   summary="Store new password for user ( send email and reset password hash)",
     *   description="Store new password for user ( send email and reset password hash)",
     *   produces={"application/json"},
     *   tags={"Reset password"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="hash",
     *     description="hash",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password",
     *     description="password",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password_confirmation",
     *     description="password_confirmation",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> []]")
    * )
    */    
    public function storeNewPassword(Request $req)
    {

        $validator = \Validator::make($req->all(), [
            'password'  => 'required|confirmed',
            'email'     => 'required',
            'hash'      => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $oauth_url     = env('OAUTH_URL_API').'user/password/reset';
        $response = CurlHelper::curlPut($oauth_url,$req->all());

        if(isset($response->success)) {
            return response()->json(['success'=>1]);
        } else {
            return response()->json(['error'=>1,'errors'=>['error_save_data'=>'Error save data']])->setStatusCode(466, 'error_save_data');
        }    
    }

    /**
     * @SWG\Get(path="/password/reset/{email}/{hash}",
     *   summary="Check if email and reset password hash are valid",
     *   description="Check if email and reset password hash are valid",
     *   produces={"application/json"},
     *   tags={"Reset password"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> []]")
     * )
    */
    public function checkNewPasswordHash($email, $hash)
    {
        $oauth_url     = env('OAUTH_URL_API').'user/password/reset/'.$email.'/'.$hash;
        $response = CurlHelper::curlGet($oauth_url);

        if(isset($response->success)) {
            return response()->json(['success'=>1]);
        } else {            
            $data['errors'] = $response->errors;
            return response()->json(['error'=>1,'errors'=>['error_hash'=>'Error email and reset password hash combination']])->setStatusCode(467, 'error_hash_data_combination');
        }
    }

    /**
     * @SWG\Get(path="/verify_account/{email}/{hash}",
     *   summary="Check if email and activation hash are valid",
     *   description="Check if email and activation hash are valid",
     *   produces={"application/json"},
     *   tags={"Create user"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> []]")
     * )
    */
    public function verifyAccount($email, $hash)
    {
        $oauth_url     = env('OAUTH_URL_API').'user/verify_account/'.$email.'/'.$hash;
        $response = CurlHelper::curlGet($oauth_url);

        if(isset($response->success)) {
            $user = User::where('email', $email)->first();
            if(!$user)
                return ['error'=>1, 'errors' => [['User not exist']]];

            $user->active = 1;
            $user->save();

            return response()->json(['success'=>1]);
        } else {            
            $data['errors'] = $response->errors;
            return response()->json(['error'=>1,'errors'=>['error_hash'=>'Error email and activation hash combination']])->setStatusCode(467, 'error_hash_data_combination');
        }
    }

    public function storeNewEmail($email, $hash)
    {
        $oauth_url     = env('OAUTH_URL_API').'user/newemail/store';
        $response = CurlHelper::curlPost($oauth_url,["email"=>$email, "hash"=>$hash]);

        if(isset($response->success)) {
            $user = User::where('user_id', $response->data->user_id)->first();
            if(!$user)
                return ['error'=>1, 'errors' => [['User not exist']]];

            $user->email = $email;
            $user->save();

            return response()->json(['success'=>1]);
        } else {
            $data['errors'] = $response->errors;
            return response()->json(['error'=>1,'errors'=>['error_hash'=>'Error email and activation hash combination']])->setStatusCode(467, 'error_hash_data_combination');
        }
    }

    /**
     * @SWG\Post(path="/password/resetForUser",
     *   summary="School admin - change leader's or student's password",
     *   description="School admin - change leadre's or student's password",
     *   produces={"application/json"},
     *   tags={"Reset password"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="user_id",
     *     description="User id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="autogenerate",
     *     description="Autogenerate [values: true and false]",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password",
     *     description="Password (required if autogenerate is false)",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function resetForUser(ChangePasswordForUserRequest $request)
    {
        if($request->get('autogenerate') == false && $request->get('password') == '')
            return response()->json()->setStatusCode(481, 'error_changing_password');

        $checkUser = User::checkIfUserExsist($request->get('user_id'));
        if(isset($checkUser['success']) && ($checkUser['user']->role == 'leader' || $checkUser['user']->role == 'student')) {
            $oauth_url = env('OAUTH_URL_API').'user/password/resetForUser';
            $response = CurlHelper::curlPost($oauth_url, $request->all());

            if(isset($response->success)) {
                UserToken::where('user_id', $request->get('user_id'))->delete();
                return response()->json(['success'=>1]);
            }

            return response()->json()->setStatusCode(481, 'error_changing_password');
        }

        return response()->json()->setStatusCode(481, 'error_changing_password');
    }

    /**
     * @SWG\Post(path="/password/change",
     *   summary="Change password",
     *   description="Change password",
     *   produces={"application/json"},
     *   tags={"Reset password"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="password",
     *     description="Password (required if autogenerate is false)",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password_confirmation",
     *     description="Password confirmation",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function changePassword(ChangePasswordRequest $request)
    {
        if($request->get('password') != $request->get('password_confirmation'))
            return response()->json()->setStatusCode(482, 'error_passwords_mismatch');

        $split_token  = explode(' ', $request->header('authorization'));
        $checkUser = User::authUser(end($split_token));

        if(isset($checkUser['success'])) {
            $input = $request->all();
            $input['user_id'] = $checkUser['user']->user_id;
            $oauth_url = env('OAUTH_URL_API').'user/password/change';
            $response = CurlHelper::curlPost($oauth_url, $input);

            if(isset($response->success)) {
                return response()->json(['success'=>1]);
            }
        }

        return response()->json()->setStatusCode(481, 'error_changing_password');
    }

    /**
     * @SWG\Post(path="/refreshToken",
     *   summary="Refresh access token",
     *   description="Refresh access token",
     *   produces={"application/json"},
     *   tags={"Refresh access token"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="access_token",
     *     description="The expired access token",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="refresh_token",
     *     description="Refresh token",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="user_data")
    * )
    */
    public function refresh(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'access_token'       => 'required',
            'refresh_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['error'=>1, 'errors' => $validator->errors()->all()];
        }

        $user = User::authUser($request->get('access_token'));
        if(!isset($user['success']))
            return response()->json()->setStatusCode(464, 'error_user_not_found');

        $user_id = $user['user']->user_id;

        $url = ENV("OAUTH_URL")."oauth/token";
        $data = 
        [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->get('refresh_token'),
            "client_id" => ENV('OAUTH_CLIENTID'),
            "client_secret" => ENV('OAUTH_SECRET'),
            "scope" => ""
        ];
        // FINAL RESPONSE
        $response = CurlHelper::curlLogin($url,$data);
        if(isset($response->error))
            return response()->json($response)->setStatusCode(498, 'error_invalid_refresh_token');

        $user_role = User::where('user_id', $user_id)->select('role')->get()->toArray();

        UserToken::createTokenFromRefresh(["token"=>$response->access_token, "id" => $user_id, "role" => $user_role[0]['role']]);
        return response()->json($response);
    }

}