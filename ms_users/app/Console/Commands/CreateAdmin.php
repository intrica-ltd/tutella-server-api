<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add_user:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin';

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
        $data = [];
        $this->line('Create admin in oauth2 microservice');
        
        $data['first_name'] = $this->ask('Admin first name ?');
        $data['last_name']  = $this->ask('Admin last name ?');
        $data['email']      = $this->ask('Admin email ?');
        $data['password']   = $this->ask('Admin password ?');
        $data['password']   = \Hash::make($data['password']);
        $data['active']     = 1;
        $data['role']       = 'admin';

        if( empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password']))
            $this->line('All fields are required. Please try again.');
        else{
            $user = new User();
            $user->createUser($data,$user);            
            $this->line('Admin has been successfuly created.');
        }
        $this->line('Bye!');
    }
}
