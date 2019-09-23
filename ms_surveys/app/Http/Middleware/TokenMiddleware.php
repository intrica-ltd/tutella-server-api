<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CurlHelper;
use Ixudra\Curl\Facades\Curl;

class TokenMiddleware
{

    public function handle($request, Closure $next)
    {
        $url = ENV("OAUTH_URL_API")."user/token/authService";
        $check = Curl::to($url)            
            ->withHeaders(["Authorization: ".$request->header('authorization'),"Accept: application/json"])
            ->asJsonResponse();
        $response = $check->get();
        
        if(!isset($response->success))
            return redirect(env('TUTELLA_WEB'));

        return $next($request);
    }
}