<?php 
namespace App;

use Zizaco\Entrust\EntrustRole;
use DB;

class Role extends EntrustRole
{
	public static function returnRoles($user_id)
	{
		$return = [];
		$roles = DB::table('role_user')->where('user_id',$user_id)->join('roles','roles.id','=','role_user.role_id')->get();

		foreach ($roles as $key => $value) {
			$return[$value->id] = $value->name;
		}
		return $return;
	}

	public static function allRoles()
    {
        $roles = DB::table('roles')->select('id', 'name', 'display_name')->get();

        return $roles;
    }
}