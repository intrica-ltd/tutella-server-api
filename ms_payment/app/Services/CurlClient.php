<?php 
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Ixudra\Curl\CurlService;

class CurlClient
{
    /**
     * @var CurlService
     */
    private $curlService;

    /**
     * @var string
     */
	private $access_token;

	private $request;

    /**
     * @throws \Exception
     */
    public function __construct(CurlService $curlService)
	{
	    $this->curlService = $curlService;

        $this->access_token = DB::connection('mysql_domain')
            ->table('appAccessToken')
            ->select('access_token')
            ->where('valid_until','>',date('Y-m-d H:i:s'))
            ->first();

        if(!$this->access_token) {
            throw new \Exception('Access token not found!');
        }

        $this->access_token = "Authorization: Bearer " . $this->access_token->access_token;

        $this->request = $this->curlService->to('')
            ->withHeaders([$this->access_token, "Accept: application/json"])
            ->asJsonResponse();
    }

	public function curlGet($url, $parametars = NULL)
	{
	    $this->request->to($url);
        
        if(!empty($parametars)) {
            $this->request->withData($parametars);
        }

        return $this->request->get();
	}

    public function curlPost($url, $parametars = NULL, $jsonFlag = false)
    {
        $this->request->to($url);

        if($jsonFlag) {
	        $this->request->asJson($jsonFlag);
        }
        
        if(!empty($parametars)) {
            $this->request->withData($parametars);
        }

        return $this->request->post();
    }

    public function curlPut($url, $parametars = NULL)
    {
        $this->request->to($url);

        if(!empty($parametars)) {
            $this->request->withData($parametars);
        }

        return $this->request->put();
    }

    public function curlDelete($url, $parametars = NULL)
    {
        $this->request->to($url);

        if(!empty($parametars)) {
            $this->request->withData($parametars);
        }

        return $this->request->delete();
    }
}
