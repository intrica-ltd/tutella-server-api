<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Surveys extends Model
{
    protected $table = 'surveys';

    public static function createSurvey($input) 
    {
        $survey = new Surveys();
        $survey->name = $input['name'];
        $survey->school_id = $input['school_id'];
        $survey->created_by = $input['created_by'];
        $survey->created_by_name = $input['created_by_name'];
        $survey->duration = $input['duration'];
        $survey->start_date = $input['start_date'];
        $survey->expires_at = date('Y-m-d H:i:s', strtotime('+'.$input['duration'].' minutes', strtotime($input['start_date'])));
        if($input['groups'] != '')
            $survey->groups = $input['groups'];
        else
            $survey->groups = '';
        $survey->nparticipants = count($input['users']);
        $survey->nquestions = count($input['questions']);
        $survey->save();

        return ['success' => 1, 'survey' => $survey];
    }
}
