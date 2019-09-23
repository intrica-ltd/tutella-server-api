<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobRole extends Model
{
    protected $table = 'role_user';
    public $timestamps = false;

    public static function getRolesByUser($user_id)
    {
        $roles = JobRole::where('user_id', '=', $user_id)->get();

        if(isset($roles)) {
            $job_roles = [];

            foreach($roles as $role) {
                $role = JobRole::get($role['role']);
                array_push($job_roles, $role);
            }

            return $job_roles;
        }

        return null;
    }

    public static function updateUserJobRoles($user_id, $roles)
    {
        JobRole::where('user_id', '=', $user_id)->delete();

        foreach($roles as $role) {
            $user_role = [
                'user_id' => $user_id,
                'role' => $role
            ];
            JobRole::create($user_role);
        }
    }
    
}
