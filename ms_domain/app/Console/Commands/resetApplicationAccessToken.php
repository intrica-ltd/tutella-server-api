<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppAccessToken;
use Ixudra\Curl\Facades\Curl;

class resetApplicationAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app_access_token:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset main application token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->line('Try to connect with OAUTH2 -   '.env('OAUTH_URL').'oauth/token');

        $oaut2_credent = [
            'grant_type' => 'client_credentials',
            'client_id' => env('OAUTH_CLIENTID'),
            'client_secret' => env('OAUTH_SECRET'),
            'scope' => '',
        ];

        $oauth_url = env('OAUTH_URL').'oauth/token';
        $this->line($oauth_url);
        $response = Curl::to($oauth_url)
            ->withData( $oaut2_credent )
            ->withHeaders(["Accept: application/json"])
            ->asJsonResponse()
            ->post();
        
        if(isset($response->access_token))
        {
            $this->line('Get access token from server and write in DB');
            AppAccessToken::where('id','>',1)->delete();
            $appToken = new AppAccessToken();
            $appToken->access_token = $response->access_token;
            $appToken->valid_until  = date('Y-m-d H:i:s',time()+$response->expires_in);
            $appToken->save();
            $this->line('Everything good...!');
        } else {
            $this->line('PROBLEM WITH OAUTH2 CONNECTION');
        }
        $this->line('Bye!');
    }
}
