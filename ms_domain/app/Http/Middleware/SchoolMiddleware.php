<?php

namespace App\Http\Middleware;

use Closure;
use Ixudra\Curl\Facades\Curl;
use App\Models\UserToken;
use App\Helpers\CurlHelper;
use App\Models\User;

class SchoolMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $access_token = $request->header('Authorization');
        
        if(empty($access_token))
            return response(['error'=>1, 'errors' => ['Unauthorized']],'401');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = UserToken::where('api_token', end($split_token))->first();
        
        $userActive = User::where('user_id', $user->user_id)->first();
        if($userActive->role != 'super_admin') {
            if($userActive->active == 0)
                return response()->json()->setStatusCode(464, 'error_user_not_active');

            $url_schools = env('SCHOOLS_URL_API').'schools/'.$userActive->school_id;
            $response_school = Curl::to($url_schools)            
                            ->withHeaders(["Authorization: Bearer ".end($split_token),"Accept: application/json"])
                            ->asJsonResponse()
                            ->get();

            if(isset($response_school->success)) {
                if($response_school->data->school->active == 0) {
                    if($user->role == 'school_admin')
                        return response()->json()->setStatusCode(457, 'error_school_deactivated');

                    return response()->json()->setStatusCode(458, 'error_user_not_active');
                } else if($response_school->data->school->active == -1) {
                    if($user->role == 'school_admin')
                        return response()->json()->setStatusCode(450, 'error_school_cancelled');

                    return response()->json()->setStatusCode(458, 'error_user_not_active');
                }
            }
        }

        return $next($request);
    }
}