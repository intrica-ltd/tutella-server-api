<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CurlHelper;
use Carbon\Carbon;
use App\Models\UserToken;


class User extends Model
{
    protected $table = 'users';

    protected $fillable   = [
        'user_id',
        'school_id',
        'first_name',
        'last_name',
        'email',
        'username',
        'active',
        'role',
        'image',
        'phone',
        'pending',
        'created_at',
        'updated_at',
        'deleted_at',
        'enrollment_code',
        'invited_by',
        'invited_by_name',
        'insta_user_id',
        'firebase_token',
        'welcome_msg',
        'fb_user',
        'insta_user'
    ];


    public static function updateUserData($user_id, $params = NULL)
    {
        $url_user = ENV("OAUTH_URL_API")."user/".$user_id;
        $response_user  = CurlHelper::curlGet($url_user);

        if($response_user->success) {
            $oldie = User::where('user_id', $user_id)->first();

            $ut = $response_user->data->user;
            $roles = $response_user->data->roles;

            if(isset($params)) {

                if(isset($params['first_name'])) {
                    $data['first_name'] = $params['first_name'];
                }

                if(isset($params['last_name'])) {
                    $data['last_name'] = $params['last_name'];
                }

                if(isset($params['role'])) {
                    $data['role'] = $params['role'];
                }

                if(isset($params['image'])) {
                    $data['image'] = $params['image'];
                }

                if(isset($params['phone'])) {
                    $data['phone'] = $params['phone'];
                } else {
                    $data['phone'] = null;
                }

                if(isset($params['school_id'])) {
                    $data['school_id'] = $params['school_id'];
                }

		if(end($roles) != 'school_admin') {
                	$data['email'] = $params['email'];
                }

                if(isset($params['username']) && $params['username'] != 'Facebook User' && $params['username'] != 'Instagram User') {
                    $data['username'] = $params['username'];
                }

                User::where('user_id', $user_id)->update($data);
            } else {
                $data_for_mongo = [
                    "user_id" => $ut->id,
                    "first_name" => $ut->first_name,
                    "last_name" => $ut->last_name,
                    "email" => $ut->email,
                    "created_at" => $ut->created_at,
                    "updated_at" => $ut->updated_at,
                    "active" => $ut->active,
                    "role" => end($roles)
                    ];
                
                User::where('user_id', $user_id)->update($data_for_mongo);
            }
        }
    }

    public static function createUser($params)
    {
        $data = [
            'user_id'           => $params['user_id'],
            'school_id'         => $params['school_id'],
            'first_name'        => $params['first_name'],
            'last_name'         => $params['last_name'],
            'username'          => $params['username'],
            'role'              => $params['role'],
            'active'            => 0,
            'pending'           => 1,
            'enrollment_code'   => $params['enrollment_code'],
            'invited_by'        => $params['invited_by'],
            'invited_by_name'   => $params['invited_by_name'],
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        if(isset($params['email'])) {
            $data['email'] = $params['email'];
        }

        if(isset($params['image'])) {
            $data['image'] = $params['image'];
        }

        $user = User::create($data);
    }

    public static function createUserData($data, $enrollment_code = false)
    {
        $ut = $data->user;
        $roles = $data->roles;

        $data_for_mongo =   [
                                "user_id" => $ut->id,
                                "first_name" => $ut->first_name,
                                "last_name" => $ut->last_name,
                                "username" => $ut->username,
                                "created_at" => $ut->created_at,
                                "updated_at" => $ut->updated_at,
                                "active" => $ut->active,
                                "role" => end($roles),
                                "enrollment_code" => $enrollment_code,
                            ];

        if(isset($ut->email))
            $data_for_mongo['email'] = $ut->email;
        
        if(isset($data->school_id))
            $data_for_mongo['school_id'] = $data->school_id;

        if($enrollment_code != false) {
            $invited_user = User::where('username', $ut->username)->where('enrollment_code', $enrollment_code)->where('pending', 1)->first();

            if($invited_user) {
                $invited_user->pending = 0;
                $invited_user->user_id = $ut->id;
                $invited_user->save();
            } else {
                $user_mongo = User::create($data_for_mongo);
                unset($data_for_mongo['user_id']);
                $data_for_mongo['id'] = $user_mongo->id;
            }
        } else if(end($roles) == 'school_admin') {
            $user_mongo = User::create($data_for_mongo);
            unset($data_for_mongo['user_id']);
            $data_for_mongo['id'] = $user_mongo->id;
        }
        return $data_for_mongo;   
        
    }

    public static function checkIfUserExsist($user_id)
    {
        $user = User::where('user_id',$user_id)->first();
        if(!empty($user)) {
            return ['success' => 1, 'user' => $user];
        } else {
            return ['error'=>1, 'errors'=> ['User not found']];
        }
    }

    public static function checkIfEmailExsist($email)
    {
        $user = User::where('email',$email)->first();
        if(empty($user)) {
            return ['success' => 1];
        } else {
            return ['error'=>1, 'errors'=> ['User not found']];
        }
    }

    public static function checkIfUsernameExsist($username)
    {
        $user = User::where('email', $username)->orwhere('username', $username)->first();
        if(empty($user)) {
            return ['success' => 1];
        } else {
            return ['error'=>1, 'errors'=> ['User not found']];
        }
    }

    public static function authUser($api_token)
    {
        $checkToken = UserToken::where('api_token',$api_token)->first();
        if($checkToken) {
            $user = User::where('user_id',$checkToken->user_id)->first();
            if(!empty($user))
                return ['success' => 1, 'user' => $user];
        }

        return ['error'=>1, 'errors'=> ['User not found']];
    }

    public static function reinviteUser($input)
    {
        $user = User::where('username', $input['username'])->where('pending', 1)->first();
        if($user) {
            $user->enrollment_code = $input['enrollment_code'];
            $user->save();
            return ['success' => 1, 'user' => $user];
        } else 
            return ['error'=>1, 'errors'=> ['User not found']];
    }

    public static function checkIfSchoolExists($school_id)
    {
        $user = User::where('school_id', $school_id)->first();
        if(!empty($user)) {
            return ['success' => 1, 'user' => $user];
        } else {
            return ['error'=>1, 'errors'=> ['User not found']];
        }
    }

    public static function updatePendingUserData($input)
    {
        $user = User::where('email', $input['old_email'])->where('enrollment_code', $input['enrollment_code'])->first();
        if(!empty($user)) {
            
            if(isset($input['first_name']))
                $user->first_name = $input['first_name'];

            if(isset($input['last_name']))
                $user->last_name = $input['last_name'];

            if(isset($input['role']))
                $user->role = $input['role'];

            if(isset($input['new_email']) && $input['new_email'] != $input['old_email'])
                $user->email = $input['new_email'];

            if(isset($input['phone']))
                $user->phone = $input['phone'];

            $user->save();

            return ['success' => 1, 'user' => $user];
        } else {
            return ['error'=>1, 'errors'=> ['User not found']];
        }
    }
    
}
