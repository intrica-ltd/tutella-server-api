<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesSeeder extends Seeder {

    public function run()
    {        
        Role::create(['name' => 'admin','display_name' => 'Administrator','description' => 'Administrator']);
        Role::create(['name' => 'assessor','display_name' => 'Assessor','description' => 'Assessor']);
        Role::create(['name' => 'trainee','display_name' => 'Trainee','description' => 'Trainee']);
    }

}