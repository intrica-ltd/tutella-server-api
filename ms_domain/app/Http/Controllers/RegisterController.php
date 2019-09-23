<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CurlHelper;
use App\Models\User;
use App\Models\EnrollmentCode;
use App\Models\SchoolPackage;
use App\Services\ActiveCampaignService;

use Socialite;

class RegisterController extends Controller
{
    private $activeCampaignService;
    public function __construct(ActiveCampaignService $activeCampaignService)
    {
        $this->activeCampaignService = $activeCampaignService;
    }
    
    /**
     * @SWG\Post(path="/user/validateInfo",
     *   summary="Validate user info.",
     *   description="Validate user info.",
     *   produces={"application/json"},
     *   tags={"Create user"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="first_name",
     *     description="First name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="last_name",
     *     description="Last name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="username",
     *     description="Username",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Email address",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="role",
     *     description="Role name ['school_admin', 'leader', 'student']",
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

    public function validateInfo(Request $request)
    {
        $user = User::where('username', $request->get('username'))->orWhere('email', $request->get('username'))->first();
        
        if($user) {
            if($user->pending != null)
                return response()->json(['pending' => $user->pending])->setStatusCode(462, 'error_user_create');
            else 
                return response()->json(['pending' => 0])->setStatusCode(462, 'error_user_create');
        }

        $oauth_url     = env('OAUTH_URL_API').'user/validateInfo';
        $response = CurlHelper::curlPost($oauth_url,$request->all());

        if(isset($response->success)) {
            return response()->json()->setStatusCode(200, 'OK');
        } else {
            return response()->json($response)->setStatusCode(462, 'error_user_create');
        }
    }

    /**
     * @SWG\Post(path="/user/register",
     *   summary="Create new user.",
     *   description="Create new user.",
     *   produces={"application/json"},
     *   tags={"Create user"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="enrollment_code",
     *     description="Enrollment code",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="first_name",
     *     description="First name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="last_name",
     *     description="Last name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Email address",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="username",
     *     description="Username",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="role",
     *     description="Role name ['school_admin', 'leader', 'student']",
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

    public function register(Request $request)
    {
        if($request->has('enrollment_code') && $request->get('enrollment_code') != '') {
            $valid_code = EnrollmentCode::where('code', $request->get('enrollment_code'))->first();
            if($valid_code && strtotime('now') < strtotime($valid_code->expiary_date)) {
                $invited_user = User::where('enrollment_code', $request->get('enrollment_code'))->whereNotNull('invited_by')->first();
                if($invited_user)
                    return response()->json()->setStatusCode(459, 'error_enrollment_code_already_used');

                $oauth_url     = env('OAUTH_URL_API').'user/store';
                $verifie_code = md5(time().$request->get('username'));
                $input = $request->all();
                $input['activation_hash'] = $verifie_code;
                $response = CurlHelper::curlPost($oauth_url, $input);
                
                if(isset($response->success)) {
                    $user_id = $response->data->user_id;
                    
                    $url = ENV("OAUTH_URL_API")."user/".$request->get('username');
                    $response_user = CurlHelper::curlGet($url);

                    if($valid_code->leader_id != null)
                        $school = User::where('user_id', $valid_code->leader_id)->first();
                    else
                        $school = User::where('user_id', $valid_code->school_admin_id)->first();

                    $response_user->data->school_id = $school->school_id;
                    User::createUserData($response_user->data, $request->get('enrollment_code'));

                    if(end($response_user->data->roles) == 'super_admin' || end($response_user->data->roles) == 'school_admin') {

                        $email_data['email'] = $request->get('email');
                        $email_data['name'] = $request->get('first_name'). ' ' . $request->get('last_name');
                        $email_data['link'] = env('TUTELLA_WEB').'/verify_account/'.$email_data['email']. '/' . $verifie_code;
                        \Mail::send('emails.confirmation', $email_data, function ($m) use ($email_data) {
                            $m->from('no-reply@tutella.com', '');
                            $m->to($email_data['email'], '')->subject('Tutella email activation.');
                        });
                    }
                    SchoolPackage::checkPackage($school->school_id);
                    
                    return response()->json()->setStatusCode(200, 'success_user_created');
                } else {
                    return response()->json($response)->setStatusCode(462, 'error_user_create');
                }
            }
        }
        return response()->json(['error' => 1])->setStatusCode(471, 'error_invalid_enrollment_code');
    }

    /**
     * @SWG\Post(path="/schoolAdmin/register",
     *   summary="Create new school admin.",
     *   description="Create new school admin.",
     *   produces={"application/json"},
     *   tags={"Create user"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="first_name",
     *     description="First name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="last_name",
     *     description="Last name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Email address",
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
    public function registerSchoolAdmin(Request $request)
    {
        $input = $request->all();
        $input['role'] = 'school_admin';
        $input['username'] = md5($input['email'].time());
        $oauth_url     = env('OAUTH_URL_API').'user/store';

        $verifie_code = md5(time().$request->get('email'));
        $input['activation_hash'] = $verifie_code;
        $response = CurlHelper::curlPost($oauth_url,$input);
        
        if(isset($response->success)) {
            $user_id = $response->data->user_id;
            
            $url = ENV("OAUTH_URL_API")."user/".$request->get('email');
            $response_user = CurlHelper::curlGet($url);
            User::createUserData($response_user->data);
            $email_data['email'] = $request->get('email');
            $email_data['name'] = $request->get('first_name'). ' ' . $request->get('last_name');
            $email_data['link'] = env('TUTELLA_WEB').'/verify_account/'.$email_data['email']. '/' . $verifie_code;
            \Mail::send('emails.confirmation', $email_data, function ($m) use ($email_data) {
                $m->from('no-reply@tutella.com', '');
                $m->to($email_data['email'], '')->subject('Tutella email activation.');
            });
            
            return response()->json()->setStatusCode(200, 'success_user_created');
        } else {
            return response()->json($response)->setStatusCode(462, 'error_user_create');
        }
    }

    /**
     * @SWG\Post(path="/user/registerEnrollmentCode",
     *   summary="Create new user via enrollment code.",
     *   description="Create new user via enrollment code.",
     *   produces={"application/json"},
     *   tags={"Create user"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="enrollment_code",
     *     description="Enrollment code",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password",
     *     description="Password",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password_confirmation",
     *     description="Password sconfirmation",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' => 1, 'data'=> ['email' => $email]]")
     * )
    */
    public function registerEnrollmentCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'enrollment_code'       => 'required',
            'password'              => 'required',
            'password_confirmation' => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        if($request->get('password') != $request->get('password_confirmation'))
            return response()->json()->setStatusCode(482, 'error_passwords_mismatch');

        $user = User::where('enrollment_code', $request->get('enrollment_code'))->where('pending', 1)->first();

        if($user) {
            $verifie_code = md5(time().$request->get('username'));
            $save_data = [
                'user_id'               => $user->user_id,
                'password'              => $request->get('password'),
                'password_confirmation' => $request->get('password_confirmation'),
                'activation_hash'       => $verifie_code
            ];

            $oauth_url     = env('OAUTH_URL_API').'user/storeInvitedUser';
            $response = CurlHelper::curlPost($oauth_url, $save_data);

            if(isset($response->success)) {
                $user->active = 1;
                $user->pending = 0;
                $user->save();

                return response()->json(['success' => 1, 'data' => ['username' => $user->username]])->setStatusCode(200, 'success_user_created');
            }

            return response()->json($response)->setStatusCode(462, 'error_user_create');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }
}