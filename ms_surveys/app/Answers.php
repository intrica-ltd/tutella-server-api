<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Answers extends Model
{
    protected $table = 'answers';

    public static function createAnswers($input)
    {
        $answers_array = [];
        foreach($input['answers'] as $answer) {
            $answers_array[] = [
                'survey_id' => $input['survey_id'],
                'question_id' => $answer['question_id'],
                'user_id' => $input['user_id'],
                'answer' => $answer['answer'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        Answers::insert($answers_array);

        return ['success' => 1];
    }
}
