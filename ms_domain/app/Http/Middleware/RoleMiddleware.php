<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\UserToken;

class RoleMiddleware
{

    public function handle($request, Closure $next, $role)
    {
        $split_token  = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
        $user = UserToken::where('api_token',end($split_token))->first();

        if(!$user) {
            return response()->json()->setStatusCode(474, 'error_not_allowed');
        }

        $userRole = $user->role;
        $allowedRoles = explode('|', $role);

        if(in_array($userRole, $allowedRoles))
            return $next($request);

        return response()->json()->setStatusCode(474, 'error_not_allowed');
    }
}