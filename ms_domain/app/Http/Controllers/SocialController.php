<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\UserToken;
use App\Helpers\CurlHelper;
use App\Models\EnrollmentCode;
use App\Models\SocialFeed;
use Facebook\Facebook;

class SocialController extends Controller
{
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required',
            'role'          => 'required'
        ]);          
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $save_data = $request->all();

        $user = User::whereEmail($save_data['email'])->first();
        if($user && $user->role != $save_data['role'])
            return response()->json()->setStatusCode(497, 'error_user_registered_with_different_role');

        $oauth_url  = env('OAUTH_URL_API').'user/facebookUser';
        $response   = CurlHelper::curlPost($oauth_url, $save_data);

        if(isset($response->success)) {

            if (!$user) {

                $user = new User([
                    'user_id'       => $response->data->user->id,
                    'username'      => $response->data->user->username,
                    'first_name'    => $save_data['first_name'],
                    'last_name'     => $save_data['last_name'],
                    'email'         => $save_data['email'],
                    'role'          => $save_data['role'],
                    'active'        => 1,
                    'fb_user'       => 1
                ]);

                if(isset($save_data['image']))
                    $user->image = $save_data['image'];

                if(isset($save_data['enrollment_code'])) {
                    $check = EnrollmentCode::where('code', $request->get('enrollment_code'))->first();

                    if($check && strtotime('now') < strtotime($check->expiary_date)) {
                        $enrollmentCodeGenerated = ($check->leader_id != null) ? $check->leader_id : $check->school_admin_id;
                        $school = User::where('user_id', $enrollmentCodeGenerated)->first();

                        $user->school_id = $school->school_id;
                        $user->enrollment_code = $request->get('enrollment_code');
                        $user->pending = 0;
                    }
                    $user->save();

                    $url = ENV("OAUTH_URL")."oauth/token";
                    $data = [
                        "grant_type"    => "password",
                        "client_id"     => ENV('OAUTH_CLIENTID'),
                        "client_secret" => ENV('OAUTH_SECRET'),
                        "username"      => $response->data->user->username,
                        "password"      => $response->data->user->pass,
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

                        $token_data = (object)[];
                        $token_data->user = $user;
                        $token_data->user->id = $user['user_id'];
                        $token_data->roles = [0 => $user['role']];
                        UserToken::createToken($response->access_token, $token_data);
                        
                        $response->user_id = $user['user_id'];
                        $response->first_name = $user['first_name'];
                        $response->last_name = $user['last_name']; 
                        $response->username = $user['username']; 
                        $response->email = $user['email']; 
                        $response->image = $user['image'];
                        $response->phone = $user['phone'];
                        $response->fb_user = $user['fb_user'];
                        $response->insta_user = $user['insta_user'];
                        $response->school_data = $school_data;
                        $response->welcome_status = 1;
                        $role = $user['role'];
                        $response->role = (object)[];
                        $roleName = Role::where('name', $role)->first();
                        if($roleName) {
                            $response->role->name = $roleName['name'];
                            $response->role->display_name = $roleName['display_name'];
                        }

                        return response()->json($response)->setStatusCode(200, 'success');
                    }
                } else {
                    $user->save();

                    return response()->json(['enrollment_code' => 0])->setStatusCode(200, 'enter_enrollment_code');
                }
            } else {

                if($user->active == 0)
                    return response()->json()->setStatusCode(464, 'error_user_not_active');

                if($user->enrollment_code == null || $user->pending == 1)
                    return response()->json(['enrollment_code' => 0])->setStatusCode(200, 'enter_enrollment_code');

                $url = ENV("OAUTH_URL")."oauth/token";
                $data = [
                    "grant_type"    => "password",
                    "client_id"     => ENV('OAUTH_CLIENTID'),
                    "client_secret" => ENV('OAUTH_SECRET'),
                    "username"      => $response->data->user->username,
                    "password"      => $response->data->user->pass,
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

                    $token_data = (object)[];
                    $token_data->user = $user;
                    $token_data->user->id = $user['user_id'];
                    $token_data->roles = [0 => $user['role']];
                    UserToken::createToken($response->access_token, $token_data);
                    
                    $response->user_id = $user['user_id'];
                    $response->first_name = $user['first_name'];
                    $response->last_name = $user['last_name']; 
                    $response->image = $user['image'];
                    $response->username = $user['username'];
                    $response->email = $user['email'];
                    $response->phone = $user['phone'];
                    $response->school_data = $school_data;
                    $response->fb_user = $user['fb_user'];
                    $response->insta_user = $user['insta_user'];
                    $response->welcome_status = 0;
                    $role = $user['role'];
                    $response->role = (object)[];
                    $roleName = Role::where('name', $role)->first();
                    if($roleName) {
                        $response->role->name = $roleName['name'];
                        $response->role->display_name = $roleName['display_name'];
                    }

                    return response()->json($response)->setStatusCode(200, 'success');
                }

            }

        }
        return response()->json($response)->setStatusCode(462, 'error_user_create');
    }

    public function instagramLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name'    => 'required',
            'last_name'     => 'required',
            'user_id'       => 'required',
            'role'          => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $save_data = $request->all();

        $user_insta = User::where('insta_user_id', $save_data['user_id'])->first();

        if(!$user_insta && !$request->has('enrollment_code'))
            return response()->json(['enrollment_code' => 0])->setStatusCode(200, 'enter_enrollment_code');

	    if($user_insta && $request->has('enrollment_code'))
            return response()->json()->setStatusCode(456, 'error_user_registered');
            

        if($user_insta && !$request->has('username'))
		    $save_data['username'] = $user_insta['username'];
        else
            $save_data['username'] = md5($save_data['user_id'].time());

        $user = User::where('username', $save_data['username'])->first();
        if($user && $user->role != $save_data['role'])
            return response()->json()->setStatusCode(497, 'error_user_registered_with_different_role');
            
        if($user && $user->insta_user_id != $save_data['user_id'])
            return response()->json()->setStatusCode(498, 'error_user_registered');

        $oauth_url  = env('OAUTH_URL_API').'user/instaUser';
        $response   = CurlHelper::curlPost($oauth_url, $save_data);

        if(isset($response->success)) {

            if (!$user) {

                $user = new User([
                    'user_id'       => $response->data->user->id,
                    'first_name'    => $save_data['first_name'],
                    'last_name'     => $save_data['last_name'],
                    'username'      => $save_data['username'],
                    'role'          => $save_data['role'],
                    'active'        => 1,
                    'insta_user_id' => $save_data['user_id'],
                    'insta_user'    => 1
                ]);

                if(isset($save_data['image']))
                    $user->image = $save_data['image'];

                    if(isset($save_data['enrollment_code'])) {
                        $check = EnrollmentCode::where('code', $request->get('enrollment_code'))->first();
    
                        if($check && strtotime('now') < strtotime($check->expiary_date)) {
                            $enrollmentCodeGenerated = ($check->leader_id != null) ? $check->leader_id : $check->school_admin_id;
                            $school = User::where('user_id', $enrollmentCodeGenerated)->first();
    
                            $user->school_id = $school->school_id;
                            $user->enrollment_code = $request->get('enrollment_code');
                            $user->pending = 0;
                        } else {
                            return response()->json()->setStatusCode(489, 'error_enrollment_code_expired');
                        }

                        $user->save();
    
                        $url = ENV("OAUTH_URL")."oauth/token";
                        $data = [
                            "grant_type"    => "password",
                            "client_id"     => ENV('OAUTH_CLIENTID'),
                            "client_secret" => ENV('OAUTH_SECRET'),
                            "username"      => $response->data->user->username,
                            "password"      => $response->data->user->pass,
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
    
                            $token_data = (object)[];
                            $token_data->user = $user;
                            $token_data->user->id = $user['user_id'];
                            $token_data->roles = [0 => $user['role']];
                            UserToken::createToken($response->access_token, $token_data);
                            
                            $response->user_id = $user['user_id'];
                            $response->first_name = $user['first_name'];
                            $response->last_name = $user['last_name']; 
                            $response->username = $user['username']; 
                            $response->email = $user['email']; 
                            $response->image = $user['image'];
                            $response->phone = $user['phone'];
                            $response->fb_user = $user['fb_user'];
                            $response->insta_user = $user['insta_user'];
                            $response->school_data = $school_data;
                            $response->welcome_status = 1;
                            $role = $user['role'];
                            $response->role = (object)[];
                            $roleName = Role::where('name', $role)->first();
                            if($roleName) {
                                $response->role->name = $roleName['name'];
                                $response->role->display_name = $roleName['display_name'];
                            }
    
                            return response()->json($response)->setStatusCode(200, 'success');
                        }
                    } else {
                        $user->save();
    
                        return response()->json(['enrollment_code' => 0])->setStatusCode(200, 'enter_enrollment_code');
                    }

            } else {

                if($user->enrollment_code == null || $user->pending == 1)
                    return response()->json(['enrollment_code' => 0])->setStatusCode(200, 'enter_enrollment_code');

                $url = ENV("OAUTH_URL")."oauth/token";
                $data = [
                    "grant_type"    => "password",
                    "client_id"     => ENV('OAUTH_CLIENTID'),
                    "client_secret" => ENV('OAUTH_SECRET'),
                    "username"      => $response->data->user->username,
                    "password"      => $response->data->user->pass,
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

                    $token_data = (object)[];
                    $token_data->user = $user;
                    $token_data->user->id = $user['user_id'];
                    $token_data->roles = [0 => $user['role']];
                    UserToken::createToken($response->access_token, $token_data);
                    
                    $response->user_id = $user['user_id'];
                    $response->first_name = $user['first_name'];
                    $response->last_name = $user['last_name']; 
                    $response->image = $user['image'];
                    $response->username = $user['username'];
                    $response->email = $user['email'];
                    $response->phone = $user['phone'];
                    $response->fb_user = $user['fb_user'];
                    $response->insta_user = $user['insta_user'];
                    $response->school_data = $school_data;
                    $response->welcome_status = 0;
                    $role = $user['role'];
                    $response->role = (object)[];
                    $roleName = Role::where('name', $role)->first();
                    if($roleName) {
                        $response->role->name = $roleName['name'];
                        $response->role->display_name = $roleName['display_name'];
                    }

                    return response()->json($response)->setStatusCode(200, 'success');
                }

            }

        }
        return response()->json($response)->setStatusCode(462, 'error_user_create');
    }

    public function enrollmentCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'enrollment_code' => 'required',
            'email' => 'required'
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
                $user->pending = 0;
                $user->active = 1;
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
                        "username"      => $response->data->user->username,
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
                            $fail = ['error'=>1, 'errors' => ['error_user_login']];
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

                        $response->user_id = $response_user->data->user->id;
                        $response->first_name = $user->first_name;
                        $response->last_name = $user->last_name; 
                        $response->image = $user->image;
                        $response->username = $user->username;
                        $response->email = $user->email;
                        $response->phone = $user->phone;
                        $response->fb_user = $user->fb_user;
                        $response->insta_user = $user->insta_user;
                        $response->school_data = $school_data;
                        $response->welcome_status = 1;
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

    /**
     * @SWG\Get(path="/documents/socialFeeds",
     *   summary="Get school page's facebook and instagram feed.",
     *   description="Get school page's facebook and instagram feed.",
     *   operationId="shwoDocumentsFeed",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Response(response="200", description="['fb_feed' => [], 'insta_feed' => []")
     * )
    */
    public function socialFeeds(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $url = ENV('SCHOOLS_URL_API') . 'schools/'.$user['user']->school_id.'/getFbPage';
            $response_page = CurlHelper::curlGet($url);

            if(isset($response_page->success) && $response_page->data->fb_access_token != null) {
                $feed = SocialFeed::where('school_id', $user['user']->school_id)->first();
                
                if(strtotime('now') > strtotime(date('Y-m-d H:i:s', strtotime($feed->updated_at . ' +2hours')))) {
                    $documents_url  = env('DOCUMENTS_URL_API').'documents/facebookFeed';
                    $response_doc   = CurlHelper::curlPost($documents_url, ['fb_access_token' => $response_page->data->fb_access_token, 'fb_page_id' => $response_page->data->fb_page_id, 'school_id' => $user['user']->school_id]);
                    
                    $feed->updated_at = date('Y-m-d H:i:s', strtotime('now'));
                    $feed->save();
                }

                $documents_url  = env('DOCUMENTS_URL_API').'documents/'.$user['user']->school_id.'/socialFeed';
                $response_doc   = CurlHelper::curlGet($documents_url);
                if(isset($response_doc->success))
                    return response()->json(['fb_feed' => $response_doc->fb_feed, 'insta_feed' => $response_doc->insta_feed, 'school_logo_id' => $response_page->data->logo_id])->setStatusCode(200, 'success');

                return response()->json(['fb_feed' => [], 'insta_feed' => []])->setStatusCode(200, 'success');
            } else if($response_page->data->fb_access_token == null) {
                return response()->json(['fb_feed' => [], 'insta_feed' => []])->setStatusCode(200, 'success');
            }

            return response()->json()->setStatusCode(480, 'error_fetching_data');
        }
    }

    /**
     * @SWG\Post(path="/schools/facebookPage",
     *   summary="Set facebook details",
     *   description="Set facebook details",
     *   operationId="facebookPage",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="fb_token",
     *     description="Facebook access token",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1, 'pages' => ['id' => page_id, 'name' => page_name]]")
    * )
    */
    public function facebookPage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'fb_token'  => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(479, 'error_school_update');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $fb = new Facebook([
                'app_id' => env('FB_CLIENT_ID'),
                'app_secret' => env('FB_CLIENT_SECRET'),
                'default_graph_version' => 'v2.2',
            ]);

            $access_token = $request->get('fb_token');
            $accessToken = '';
            $oAuth2Client = $fb->getOAuth2Client();
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($access_token);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                return response()->json()->setStatusCode(494, 'error_obtaining_token');
            }

            try {
                $response = $fb->get(
                    '/me/accounts',
                    $accessToken->getValue()
                );
            } catch(FacebookExceptionsFacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookExceptionsFacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            
            $pages = [];
            $graphNode = $response->getGraphEdge();
            foreach($graphNode as $g) {
                $pages[] = [
                    'id' => $g['id'],
                    'name' => $g['name']
                ];
            }

            $feed = new SocialFeed();
            $feed->school_id = $user['user']->school_id;
            $feed->save();
            
            $input['token'] = $accessToken->getValue();
            $input['school_id'] = $user['user']->school_id;

            $url = ENV('SCHOOLS_URL_API') . 'schools/facebookPage';
            $response = CurlHelper::curlPost($url, $input);
            
            if(isset($response->success)) {
                $documents_url  = env('DOCUMENTS_URL_API').'documents/facebookFeed';
                $response_doc   = CurlHelper::curlPost($documents_url, ['fb_access_token' => $accessToken->getValue(), 'fb_page_id' => $graphNode[0]['id'], 'school_id' => $user['user']->school_id]);
                
                return response()->json(['success' => 1, 'pages' => $pages])->setStatusCode(200, 'success');
            }

            return response($response)->json()->setStatusCode(494, 'error_obtaining_token');
        }
    }

    /**
     * @SWG\Post(path="/schools/facebookPage/save",
     *   summary="Set facebook page details",
     *   description="Set facebook page details",
     *   operationId="facebookPageSave",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="page_id",
     *     description="Facebook page ID",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1]")
    * )
    */
    public function facebookPageSave(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'page_id'  => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(479, 'error_school_update');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {

            $url = ENV('SCHOOLS_URL_API') . 'schools/'.$user['user']->school_id.'/getFbPage';
            $response_page = CurlHelper::curlGet($url);
            if(!isset($response_page->success))
                return response()->json()->setStatusCode(480, 'error_fetching_data');

            $fb = new Facebook([
                'app_id' => env('FB_CLIENT_ID'),
                'app_secret' => env('FB_CLIENT_SECRET'),
                'default_graph_version' => 'v2.2',
            ]);
    
            try {
                $response = $fb->get(
                    '/'.$request->get('fb_page_id').'/'.$request->get('page_id').'?fields=link',
                    $response_page->data->fb_access_token
                );
            } catch(FacebookExceptionsFacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookExceptionsFacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $graphNode = $response->getGraphObject();

            $input['page_url'] = $graphNode['link'];
            $input['school_id'] = $user['user']->school_id;
            $input['page_id'] = $request->get('page_id');
            $input['token'] = $response_page->data->fb_access_token;
            $url = ENV('SCHOOLS_URL_API') . 'schools/facebookPage';
            $response = CurlHelper::curlPost($url, $input);
            
            if(isset($response->success)) {
                $documents_url  = env('DOCUMENTS_URL_API').'documents/facebookFeed';
                $response_doc   = CurlHelper::curlPost($documents_url, ['fb_access_token' => $input['token'], 'fb_page_id' => $input['page_id'], 'school_id' => $user['user']->school_id]);
                
                return response()->json(['success' => 1])->setStatusCode(200, 'success');
            }
        }
     
        return response($response)->json()->setStatusCode(494, 'error_obtaining_token');
    }

    public function validateEmail(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id'   => 'required',
            'email'     => 'required',
            'role'      => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(479, 'error_school_update');
        }

        $save_data = $request->all();

        $user_insta = User::where('insta_user_id', $save_data['user_id'])->first();
        if(!$user_insta && !$request->has('email'))
            return response()->json(['email' => 0])->setStatusCode(200, 'enter_email');

	 if($user_insta && $request->has('email') && $user_insta->email != $save_data['email'])
                return response()->json()->setStatusCode(456, 'error_user_registered');


        if($user_insta && !$request->has('email'))
            $save_data['email'] = $user_insta['email'];

        $user = User::whereEmail($save_data['email'])->first();
        if($user && $user->role != $save_data['role'])
            return response()->json()->setStatusCode(497, 'error_user_registered_with_different_role');
            
        if($user && $user->insta_user_id != $save_data['user_id'])
            return response()->json()->setStatusCode(498, 'error_user_registered');

        return response()->json(['success' => 1])->setStatusCode(200, 'success');
    }
}
