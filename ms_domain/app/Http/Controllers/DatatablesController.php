<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\CurlHelper;

class DatatablesController extends Controller
{
    /**
     * @SWG\Get(path="/user/all",
     *   summary="Show all users.",
     *   description="Show user table data.",
     *   operationId="shwoUserTable",
     *   produces={"application/json"},
     *   tags={"User"},
     *   @SWG\Response(response="200", description="[user_data]")
     * )
    */
    public function listAllUsers(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        $users_sql =    User::join('roles', 'roles.name', 'users.role')
                            ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.username', 'users.email', 'users.role', 'users.active', 'users.pending', 'users.deleted_at', 'users.phone', 'users.image_id', 'users.enrollment_code', 'roles.display_name as role_display_name', 'invited_by_name as invited_by', 'users.created_at', 'users.fb_user', 'users.insta_user')
                            ->where('users.deleted_at', '=', NULL)
                            ->where('users.role', '!=', 'super_admin')
                            ->where('users.role', '!=', 'school_admin')
                            ->where('users.school_id', $user['user']->school_id)
                            ->orderBy('users.id', 'desc');

        $users_total =  User::where('deleted_at', '=', '')
                            ->where('role', '!=', 'super_admin')
                            ->where('role', '!=', 'school_admin')
                            ->where('school_id', $user['user']->school_id)
                            ->count();

        $users_pending = User::where('pending', '=', 1)
                            ->where('role', '!=', 'super_admin')
                            ->where('role', '!=', 'school_admin')
                            ->where('school_id', $user['user']->school_id)
                            ->where('deleted_at', '=', '')
                            ->count();

        $users = $users_sql->get();

        $data = [
            'users' => $users,
            'total' => $users_total,
            'pending' => $users_pending
        ];

        return response()->json($data);
    }
    /**
     * @SWG\Get(path="/user/light",
     *   summary="Show all active users with less data.",
     *   description="Show active users with less data.",
     *   operationId="shwoUserTableLight",
     *   produces={"application/json"},
     *   tags={"User"},
     *   @SWG\Response(response="200", description="[user_data]")
     * )
    */
    public function light(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        $users_sql =    User::join('roles', 'roles.name', 'users.role')
                            ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role')
                            ->where('users.deleted_at', '=', NULL)
                            ->where('users.role', '!=', 'super_admin')
                            ->where('users.role', '!=', 'school_admin')
                            ->where('users.school_id', $user['user']->school_id)
                            ->where('active', 1);

        $users = $users_sql->get();

        $data = [
            'users' => $users
        ];

        return response()->json($data);
    }

    /**
     * @SWG\Get(path="/groups/activeUsers",
     *   summary="Show all active users.",
     *   description="Show all active users.",
     *   operationId="shwoUserTable",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Response(response="200", description="[user_data]")
     * )
    */
    public function activeUsers(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        $users = User::join('roles', 'roles.name', 'users.role')
                    ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role', 'roles.display_name as role_display_name')
                    ->where('users.deleted_at', '=', NULL)
                    ->where('users.role', '!=', 'super_admin')
                    ->where('users.role', '!=', 'school_admin')
                    ->where('users.school_id', $user['user']->school_id)
                    ->where(function($query){
                        $query->where('users.active', 1);
                        $query->orWhere('users.pending', 1);
                    })
                    ->get();
        
        return response()->json(['users' => $users]);
    }
}
