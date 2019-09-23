<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use  HasApiTokens,Notifiable,SoftDeletes;
    use  EntrustUserTrait{EntrustUserTrait::restore insteadof SoftDeletes;}

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name', 'email', 'password','active', 'username'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function createUser($input, $user)
    {
        $role = \DB::table('roles')->where('name',$input['role'])->first();
  
        if(empty($role))
        {
            return ['error' => 1, 'error_type' => 'role_not_found'];
        }

        $user->first_name               = $input['first_name'];
        $user->last_name                = $input['last_name'];
        $user->username                 = $input['username'];
        
        if(isset($input['email']))
            $user->email                    = $input['email'];
        
        $user->password                 = bcrypt($input['password']);
        $user->activation_hash          = $input['activation_hash'];

        if($role->name == 'leader' || $role->name == 'student')
            $user->active = 1;
        else
            $user->active = 0;
            
        $user->save();      

        $user->attachRole($role->id);
        
        return ['success'=>1, 'user_id' => $user->id, 'code'=>$user->activation_code];
    }

    public function createInvitedUser($input, $user)
    {
        $role = \DB::table('roles')->where('name',$input['role'])->first();
  
        if(empty($role))
        {
            return ['error' => 1, 'error_type' => 'role_not_found'];
        }
  
        $user->first_name               = $input['first_name'];
        $user->last_name                = $input['last_name'];
        $user->username                 = $input['username'];

        if(isset($input['email']))
            $user->email                    = $input['email'];

        $user->password                 = bcrypt(mt_rand(100000,999999));
        $user->active = 0;
            
        $user->save();      

        $user->attachRole($role->id);
        
        return ['success'=>1, 'user_id' => $user->id];
    }

    public function findForPassport($username) {
        return $this->where('username', $username)->first();
    }

}
