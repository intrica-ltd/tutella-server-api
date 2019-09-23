<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Assignees extends Model
{
    protected $table = 'assignees';

    public static function createAssignees($input)
    {
        $assignees_array = [];
        foreach($input['users'] as $key => $value) {
            $assignees_array[] = [
                'survey_id' => $input['survey_id'],
                'user_id' => $value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        Assignees::insert($assignees_array);

        return ['success' => 1];
    }
}
