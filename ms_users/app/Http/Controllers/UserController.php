<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\RoleUser;
use Hash;
use Datatables;
use Auth;
use DB;
use App\Http\Requests\InviteUserRequest;

/** @SWG\Swagger(
 *     basePath="http://www.auth.train.com",
 *     host="laravel.localhost",
 *     schemes={"http"},
 *     @SWG\Info(
 *         version="1.0",
 *         title="OAUTH2 API",
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

class UserController extends Controller
{ 

    /**
     * @SWG\Get(
     *   path="/user",
     *   summary="List all users",
     *   tags={"User"},
     *   @SWG\Response(
     *     response=200,
     *     description="['success'=>1]"
     *   )
     * )
     */
    public function index()
    {
        $return_users   =   [];
        $users = User::all();
        foreach ($users as $key => $value) {
            $return_users[(int)$value->id] = $value;
        }
        return ['success' =>1, 'data'=> ['users' => $return_users ]];
    }

        /**
     * @SWG\Post(path="/user/invite",
     *   tags={"User"},
     *   summary="Create user (pending) and send invitation mail",
     *   description="Invite user to system. Init user in database and wait to be activated later  when all data will be field.",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="first_name",
     *     description="First Name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="last_name",
     *     description="Last Name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="role",
     *     description="Role",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */

    public function store(InviteUserRequest $request)
    {

        $user       = new User();
        $role       = $request->get('role');
        
        $response   = $user->createUser($request->toArray(),$user);
        
        if(isset($response['success'])){
            $user = User::where('id',$response['user_id'])->first();
            
            return ['success' => 1, 'data'=> ['user_id' => $response['user_id']] ];
        }
        else
            return $response;
    }

    public function validateInfo(InviteUserRequest $request)
    {
        return ['success' => 1];   
    }

    /**
     * @SWG\Get(path="/user/invite/{email}/{activation_hash}",
     *   tags={"User"},
     *   summary="User details",
     *   description="Get user details.",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */

    public function showFromActivationHash($email, $activation_hash)
    {

        $user = User::where('email',$email)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exsist']]];

        if($user->active == 1)
            return ['error'=>1, 'errors' => [['User already active']]];

        if($user->activation_hash != $activation_hash)
            return ['error'=>1, 'errors' => [['Wrong activation hash']]];

        return ['success' =>1, 'data' => ['user'=> $user, 'roles'=>Role::returnRoles($user->id) ]];
    }

    /**
     * @SWG\Get(path="/user/invite/{email}/{activation_hash},{digitCode}",
     *   tags={"User"},
     *   summary="User details",
     *   description="Get user details.",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */

    public function showFromActivationCode($email, $activation_hash,$digitCode)
    {

        $user = User::where('email',$email)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exsist']]];

        if($user->active == 1)
            return ['error'=>1, 'errors' => [['User already active']]];

        if($user->activation_hash != $activation_hash)
            return ['error'=>1, 'errors' => [['Wrong activation hash']]];

        if($user->activation_code != $digitCode)
            return ['error'=>1, 'errors' => [['Wrong activation code']]];

        return ['success' =>1, 'data' => ['user'=> $user, 'roles'=>Role::returnRoles($user->id) ]];
    }


   /**
     * @SWG\Get(path="/user/{id or email}",
     *   tags={"User"},
     *   summary="User details",
     *   description="Get user details.",
     *   operationId="get User details",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */
    public function show(Request $req,$id)
    {

        if($req->has('withTrashed'))
        {
            $user = User::where('id',$id)->withTrashed()->first();
        } else {
            $user = User::where('id',$id)->first();
        }

        
        if(!$user){
            if($req->has('withTrashed'))
            {   
                $user = User::where('email',$id)->withTrashed()->first();
            } else {
                $user = User::where('email',$id)->first();
            }
        }

        if(!$user){
            if($req->has('withTrashed'))
            {   
                $user = User::where('username',$id)->withTrashed()->first();
            } else {
                $user = User::where('username',$id)->first();
            }
            if(!$user){
                return ['error'=>1, 'errors' => [['User not exsist']]];
            }
        }

        return ['success' =>1, 'data' => ['user'=> $user, 'roles'=>Role::returnRoles($user->id) ]];
    }

    /**
     * @SWG\Put(path="/user/invite",
     *   tags={"User"},
     *   summary="Activate user with activation hash and email address",
     *   description="Activate user with activation hash sent in email.",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="activation_hash",
     *     description="Activation hash from email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */
    public function edit(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'activation_hash'  => 'required',
            'email' => 'required|email',
            'password'=>'required|confirmed'
        ]);

        if ($validator->fails()) {
            return ['error'=>1, 'errors' => $validator->errors()->all()];
        }

        $user = User::where('email',$request->get('email'))
                    ->where('activation_hash',$request->get('activation_hash'))
                    ->first();

        if(!$user)
            return ['error'=>1, 'errors' => [['User not exsist']]];

        if($user->active == 1)
            return ['error'=>1, 'errors' => [['User already active']]];

        $user->password         = Hash::make($request->get('password')); 
        $user->active           = 1;
        $user->activation_hash  = NULL;
        $user->activation_code  = NULL;
        $user->save();

        return ['success' =>1, 'data' => ['user_id'=> $user->id ]];
    }

    /**
     * @SWG\Put(path="/user/storenewemail",
     *   tags={"User"},
     *   summary="Change user email address with activation hash",
     *   description="Change user email address with activation hash.",
     *   operationId="changeEmail",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="activation_hash",
     *     description="Activation hash from email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */

    public function storeNewEmail(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'hash'  => 'required',
            'email' => 'required|email|unique:users'
        ]);

        if ($validator->fails()) {
            return ['error'=>1, 'errors' => $validator->errors()->all()];
        }

        $user = User::where('new_email',$request->get('email'))->where('reset_email_hash',$request->get('hash'))->first();

        if(!$user)
            return ['error'=>1, 'errors' => [['Expired or wrong hash']]];

        if(empty($user->reset_email_valid) || $user->reset_email_valid > date('Y-m-d H:i:s'))
        {
            $user->email                = $request->get('email'); 
            $user->new_email            = NULL;
            $user->reset_email_hash     = NULL;
            $user->reset_email_valid    = NULL;
            $user->save();  

            return ['success' =>1, 'data' => ['user_id'=> $user->id ]];
        }

        return ['error'=>1, 'errors' => [['Expired or wrong hash']]];
    }


    /**
     * @SWG\Put(path="/user/{id}",
     *   tags={"User"},
     *   summary="Edit user data",
     *   description="Update data for user",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="name",
     *     description="Name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="active",
     *     description="Active",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="location",
     *     description="Location",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="job_title",
     *     description="Job title",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="password",
     *     description="With password confirmation",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['user_id' => user->id ]]")
     * )
     */

    public function update(Request $request, $id)
    {
        $user = User::where('id',$id)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exsist']]];

        if($request->has('first_name'))
            $user->first_name = $request->get('first_name');
        if($request->has('last_name'))
            $user->last_name = $request->get('last_name');
        if($request->has('active'))
            $user->active = $request->get('active');

        if($request->has('password'))
        {           
            $validator = \Validator::make($request->all(), ['password'=>'required|confirmed']);
            if ($validator->fails()) {
                return ['error'=>1, 'errors' => $validator->errors()->all()];
            } else {
                $user->password = Hash::make($request->get('password')); 
            }
        }

        if($request->has('role'))
        {
            $exists = Role::where('name', '=', $request->get('role'))->exists();
            if($exists) {
                $role_id = Role::where('name', '=', $request->get('role'))->first()->id;

                RoleUser::where('user_id', $id)->delete();
                RoleUser::insert(['user_id' => $id, 'role_id' => $role_id]);
            }
        }

        if($request->has('username')){            
            if($request->get('username') != $user->username && $request->get('username') != 'Facebook User' && $request->get('username') != 'Instagram User') {
                $validator = \Validator::make($request->all(), [
                    'username' => 'required|unique:users,username|unique:users,email',
                ]);
                
                if ($validator->fails()) {
                    return ['error'=>1, 'errors' => $validator->errors()->all()];
                }

                $user->username = $request->get('username');
            }
        }    

        if($request->get('email') != $user->email){
            if($request->get('email') != null && $request->get('email') != '') {
                $validator = \Validator::make($request->all(), [
                    'email' => 'required|unique:users|email',
                ]);
                
                if ($validator->fails()) {
                    return ['error'=>1, 'errors' => $validator->errors()->all()];
                }
            }
            $user->email            = $request->get('email');
            // $user->new_email            = $request->get('email');
            // $user->reset_email_hash     = md5(time().$request->get('email'));
            // $user->reset_email_valid    = date('Y-m-d H:i:s',strtotime("+48 hours"));
        }       

        $user->save();
        return ['success'=>1, 'data'=>['user'=>$user]];
    }

    /**
     * @SWG\Delete(path="/user/{id}",
     *   tags={"User"},
     *   summary="Soft delete user",
     *   description="Soft delete user.",
     *   operationId="createUser",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="['success' =>1]")
     * )
     */

    public function destroy(Request $request, $id)
    {
        $user = User::where('id',$id)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exist']]];

        $user->email = time().'@'.$user->email;
        $user->active = 0;
        $user->activation_hash = NULL;
        $user->activation_code = NULL;
        $user->deleted_at = date('Y-m-d H:i:s');
        $user->deleted_by = $request->get('deleted_by');
        $user->save();

        return ['success' =>1, 'data'=>['user'=>$user]];
    }

    public function deactivate($id)
    {
        $user = User::where('id', $id)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exist']]];

        $user->active = 0;
        $user->save();

        return ['success' =>1, 'data'=>['user'=>$user]];
    }

    public function activate($id)
    {
        $user = User::where('id', $id)->first();
        if(!$user)
            return ['error'=>1, 'errors' => [['User not exist']]];

        $user->active = 1;
        $user->save();

        return ['success' =>1, 'data'=>['user'=>$user]];
    }

    public function verifyAccount($email, $hash)
    {
        $user = User::where('email',$email)->where('activation_hash',$hash)->first();
        if(!$user)
           return ['error'=>1, 'errors' => [['Wrong reset password hash']]];
        
        $user->active = 1;
        $user->save();
   
        return ['success'=>1];
    }

    public function inviteUser(Request $request)
    { 
        $input = $request->all();

        $user = User::where('username', $request->get('username'))->first();
        if(!$user) {
            $new_user = new User();
            $user = $new_user->createInvitedUser($request->all(), $new_user);
        }

        $data = ['code'=>$input['enrollment_code'], 'username'=>$input['username']];
        return ['success'=>1, 'data' => $user];
        
    }
    
    public function storeInvitedUser(Request $request)
    {
        $user = User::where('id', $request->get('user_id'))->first();

        if(!$user)
            return ['error'=>1, 'errors' => [['User not exsist']]];

        $user->password = bcrypt($request->password);
        $user->active = 1;
        $user->save();

        return ['success'=>1];
    }

    public function facebookUser(Request $request)
    {
        $input = $request->all();
        $user = User::where('email', $input['email'])->first();

        $new_password = mt_rand(10000,99999);

        if($user) {
            if($user->old_password == null)
                $user->old_password = $user->password;

            $user->password = bcrypt($new_password);
            $user->active = 1;
            $user->save();
        }

        if(!$user) {
            $username = md5(time().$input['email']);
            $role = \DB::table('roles')->where('name',$input['role'])->first();

            if(empty($role))
            {
                return ['error' => 1, 'error_type' => 'role_not_found'];
            }

            $user = User::create([
                'username'      => $username,
                'first_name'    => $input['first_name'],
                'last_name'     => $input['last_name'],
                'email'         => $input['email'],
                'password'      => bcrypt($new_password),
                'active'        => 1
            ]);

            $user->attachRole($role->id);
        } else {
            if($user->fb_access == null) {
                $user->old_password = $user->password;
                $user->save();
            }
        }

        $user->pass = $new_password;

        return ['success'=>1, 'data' => ['user' => $user]];
    }

    public function instaUser(Request $request)
    {
        $input = $request->all();
        $user = User::where('username', $input['username'])->first();

        $new_password = mt_rand(10000,99999);

        if($user) {
            if($user->old_password == null)
                $user->old_password = $user->password;

            $user->password = bcrypt($new_password);
            $user->active = 1;
            $user->save();
        }

        if(!$user) {
            $username = $input['username'];
            $role = \DB::table('roles')->where('name',$input['role'])->first();

            if(empty($role))
            {
                return ['error' => 1, 'error_type' => 'role_not_found'];
            }

            $user = User::create([
                'username'      => $username,
                'first_name'    => $input['first_name'],
                'last_name'     => $input['last_name'],
                'username'         => $input['username'],
                'password'      => bcrypt($new_password),
                'active'        => 1
            ]);

            $user->attachRole($role->id);
        } else {
            if($user->fb_access == null) {
                $user->old_password = $user->password;
                $user->save();
            }
        }

        $user->pass = $new_password;

        return ['success'=>1, 'data' => ['user' => $user]];
    }

    public function authService()
    {
        return ['success'=>1];
    }

    public function removeUser($user_id)
    {
        $user = User::where('id', $user_id)->forceDelete();
        RoleUser::where('user_id', $id)->delete();

        return ['success' => 1];
    }

}
