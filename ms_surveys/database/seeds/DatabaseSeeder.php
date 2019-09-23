<?php

use Illuminate\Database\Seeder;
use App\SurveyAnswerTypes;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SurveyTypesTableSeeder::class);
    }
}

class SurveyTypesTableSeeder extends Seeder {

    public function run()
    {
        $types = [
            ['name' => 'smiley', 'display_name' => 'Smiley'],
            ['name' => 'thumbs', 'display_name' => 'Thumbs'],
            ['name' => 'stars', 'display_name' => 'Stars']
        ];
        
        SurveyAnswerTypes::insert($types);
    }

}