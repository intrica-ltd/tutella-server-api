<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CurlHelper;
use Carbon\Carbon;


class UserToken extends Model
{
    protected $table = 'user_token';

    protected $fillable   = [
        'user_id',
        'role',
        'api_token',
        'expires_at'
    ];

    public static function createToken($token, $input)
    {
        $url_user = ENV("OAUTH_URL_API")."user/".$input->user->id;
        $response_user  = CurlHelper::curlGet($url_user);

        if(isset($response_user->success)) {
            UserToken::create(['user_id'=>$input->user->id, 'role'=>end($input->roles), 'api_token'=>$token, 'expires_at'=>date('Y-m-d H:i:s',strtotime("+5 day", time()))]);
        }
    }

    public static function createTokenFromRefresh($input)
    {
        $url_user = ENV("OAUTH_URL_API")."user/".$input['id'];
        $response_user  = CurlHelper::curlGet($url_user);

        if($response_user->success) {
            UserToken::create(['user_id'=>$input['id'], 'role'=>$input['role'], 'api_token'=>$input['token'], 'expires_at'=>date('Y-m-d H:i:s',strtotime("+5 day", time()))]);
        }
    }
    
}
