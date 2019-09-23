<?php 
namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use DB;

use Ixudra\Curl\Facades\Curl;
use App\Models\AppAccessToken;

class CurlHelper extends Model
{
   
	private $access_token;

	public static function getAppAccessToken()
	{		        
        // ADD THIS AS STATIC FUNCTION OR AS SERVICE
        $app_at = AppAccessToken::where('valid_until','>',date('Y-m-d H:i:s'))->first();
        if($app_at)
            return $app_at->access_token;
        else
            return $access_token = '';
	}

	public static function curlGet( $oauth_url, $parametars = NULL)
	{
		$access_token 	= CurlHelper::getAppAccessToken();
		$access_token  	= "Authorization: Bearer ".$access_token; 
        
        $response = Curl::to($oauth_url)            
            ->withHeaders([$access_token,"Accept: application/json"])
            ->asJsonResponse();
        
        if(!empty($parametars))
        	$response = $response->withData($parametars);

        return $response->get();
	}

    public static function curlPost( $oauth_url, $parametars = NULL, $jsonFlag = false)
    {
        $access_token   = CurlHelper::getAppAccessToken();
        $access_token   = "Authorization: Bearer ".$access_token; 
        
        $response = Curl::to($oauth_url)
            ->withHeaders([$access_token,"Accept: application/json"])
            ->asJsonResponse();

        if($jsonFlag) {
	        $response->asJson($jsonFlag);
        }
        
        if(!empty($parametars))
            $response = $response->withData($parametars);

        return $response->post();
    }

    public static function curlPut( $oauth_url, $parametars = NULL)
    {
        $access_token   = CurlHelper::getAppAccessToken();
        $access_token   = "Authorization: Bearer ".$access_token; 
        
        $response = Curl::to($oauth_url)            
            ->withHeaders([$access_token,"Accept: application/json"])
            ->asJsonResponse();
        
        if(!empty($parametars))
            $response = $response->withData($parametars);

        return $response->put();
    }

    public static function curlDelete( $oauth_url, $parametars = NULL)
    {
        $access_token   = CurlHelper::getAppAccessToken();
        $access_token   = "Authorization: Bearer ".$access_token; 
        
        $response = Curl::to($oauth_url)            
            ->withHeaders([$access_token,"Accept: application/json"])
            ->asJsonResponse();
        
        if(!empty($parametars))
            $response = $response->withData($parametars);

        return $response->delete();
    }

    public static function curlLogin( $oauth_url, $parametars = NULL)
    {
        $access_token   = CurlHelper::getAppAccessToken();
        $access_token   = "Authorization: Bearer ".$access_token; 
        
        $response = Curl::to($oauth_url)            
            ->withHeaders([$access_token,"Accept: application/json"])
            ->asJsonResponse();
        
        if(!empty($parametars))
            $response = $response->withData($parametars);

        return $response->post();
    }
}
