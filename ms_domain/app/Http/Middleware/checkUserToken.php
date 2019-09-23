<?php

namespace App\Http\Middleware;

use Closure;
use Ixudra\Curl\Facades\Curl;
use App\Models\UserToken;
use App\Helpers\CurlHelper;
use App\Models\User;

class checkUserToken
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
        if($user && time() < strtotime($user->expires_at)) {

            $url = ENV("OAUTH_URL_API")."user/token/checkMyAccessToken";
            $response = Curl::to($url)            
                ->withHeaders(["Authorization: Bearer ".end($split_token),"Accept: application/json"])
                ->asJsonResponse()
                ->get();
                
            if(isset($response->error))
            {
                return response(['error'=>1, 'errors' => ['Unauthorized']],'401');    
            } else {
                return $next($request);
            }
        }

        return response()->json()->setStatusCode('499', 'access_token_expired');
    }
}