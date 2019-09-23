<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Questions extends Model
{
    protected $table = 'questions';
    protected $fillable = ['survey_id', 'question', 'answer_type'];

    public static function createQuestions($input)
    {
        $questions_array = [];
        foreach($input['questions'] as $qst) {
            $questions_array[] = [
                'survey_id' => $input['survey_id'],
                'question' => $qst['qst'],
                'answer_type' => $qst['answer_type'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        Questions::insert($questions_array);
        
        return ['success' => 1];
    }
}
