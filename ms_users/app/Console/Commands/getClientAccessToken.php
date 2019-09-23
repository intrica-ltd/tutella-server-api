<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class getClientAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:getClientAccessCode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get access token for codeception testing';

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
        $this->line('Find password grant client and add access token in codeception test table');
        $client = \DB::table('oauth_clients')->where('password_client',1)->where('revoked',0)->first();
        if(!empty($client))
        {
            $data =
            [
                'grant_type'        => 'client_credentials',
                'client_id'         => $client->id,
                'client_secret'     => $client->secret,
                'scope'             => '',
            ];
            $response = Curl::to(env('APP_URL').'/oauth/token')->withData($data)->asJson()->post();
            
            $data = 
            [
                'type' => 'client_access_token',
                'value' => json_encode(['client_id'=>$client->id,'access_token'=>$response->access_token])
            ];
            \DB::table('z_testcodeception')->insert($data);

            $test = \DB::table('z_testcodeception')->first();
            dd($test);

        } else
        {
            $this->line('Password grant client not found! Please check oauth_client table.');
        }
        
        
    }
}
