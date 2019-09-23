<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answers;
use App\AnswerTypes;
use App\Assignees;
use App\Questions;
use App\Surveys;
use DB;

class SurveysController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $res_survey = Surveys::createSurvey($input);
        if(!isset($res_survey['success']))
            return $res_survey;

        $res_questions = Questions::createQuestions(['survey_id' => $res_survey['survey']['id'], 'questions' => $input['questions']]);
        if(!isset($res_questions['success']))
            return $res_questions;

        $res_assignees = Assignees::createAssignees(['survey_id' => $res_survey['survey']['id'], 'users' => $input['users']]);
        if(!isset($res_assignees['success'])) 
            return $res_assignees;

        return ['success' => 1, 'survey' => $res_survey['survey']];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $survey =           Surveys::where('surveys.id', $id)
                                ->where('school_id', $request->get('school_id'))
                                ->select('name', 'created_by_name', 'start_date', 'duration', 'expires_at')
                                ->get();
        if(!$survey)
            return ['error' => 1];
            
        $expired = (date('Y-m-d H:i:s') < $survey[0]->start_date) ? 0 : 1;
        $total_assignees =  Assignees::where('survey_id', $id)->count(); 
     
        if($request->get('role') != 'student') {

            $surveys_done = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('surveys.id', $id)
                                ->where('assignees.status', 1)
                                ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                ->count();
            
            $surveys_canceled = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                    ->where('surveys.id', $id)
                                    ->where('assignees.status', -1)
                                    ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                    ->count();

            $surveys_expired =  Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                    ->where('surveys.id', $id)
                                    ->where('assignees.status', 0)
                                    ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                    ->count();

            $assignees = Assignees::where('survey_id', $id)
                            ->pluck('user_id')
                            ->toArray();

            $questions = Questions::where('questions.survey_id', $id)
                            ->select('questions.id', 'questions.question', 'questions.answer_type')
                            ->get()
                            ->toArray();

            foreach($questions as $key => $question) {

                if($question['answer_type'] == 4) {
                    $answers = Answers::where('question_id', $question['id'])->select('answer')->get()->toArray();
                    $questions[$key]['answers'] = $answers;
                } else {

                    $return = [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0
                    ];
                    
                    $answers = Answers::where('question_id', $question['id'])->get();
                    foreach($answers as $answer) {
                        $return[$answer['answer']] += 1;
                    }
                    
                    if(count($answers) > 0) {
                        $questions[$key]['very_satisfied'] = $return[5];
                        $questions[$key]['satisfied'] = $return[4];
                        $questions[$key]['neither_satisfied_nor_dissatisfied'] = $return[3];
                        $questions[$key]['dissatisfied'] = $return[2];
                        $questions[$key]['very_dissatisfied'] = $return[1];
                        $questions[$key]['total_answers'] = count($answers);
                    } else {
                        $questions[$key]['very_satisfied'] = 0;
                        $questions[$key]['satisfied'] = 0;
                        $questions[$key]['neither_satisfied_nor_dissatisfied'] = 0;
                        $questions[$key]['dissatisfied'] = 0;
                        $questions[$key]['very_dissatisfied'] = 0;
                        $questions[$key]['total_answers'] = 0;
                    }
                }
            }

            return [
                'success' => 1,
                'data' => [
                    'survey' => $survey,
                    'total_questions' => count($questions),
                    'total_participants' => $total_assignees, 
                    'surveys_done' => $surveys_done, 
                    'surveys_canceled' => $surveys_canceled, 
                    'surveys_expired' => $surveys_expired,
                    'questions' => $questions,
                    'participants' => $assignees,
                    'expired' => $expired
                ]
            ];
        } else {
            $assignee = Assignees::where('survey_id', $id)
                            ->where('user_id', $request->get('user_id'))
                            ->first();
            
            if($assignee && $assignee->status == 1)
                $questions = Questions::join('answers', 'answers.question_id', 'questions.id')
                                ->where('answers.user_id', $request->get('user_id'))
                                ->where('questions.survey_id', $id)
                                ->select('questions.id', 'questions.question', 'questions.answer_type', 'answers.answer')
                                ->get()
                                ->toArray();
            else
                $questions = Questions::where('questions.survey_id', $id)
                                ->select('questions.id', 'questions.question', 'questions.answer_type')
                                ->get()
                                ->toArray();

            return [
                'success' => 1,
                'data' => [
                    'survey' => $survey,
                    'total_questions' => count($questions),
                    'total_participants' => $total_assignees,
                    'questions' => $questions,
                    'expired' => $expired
                ]
            ];
        }        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $input = $request->all();
        $survey = Surveys::where('id', $input['survey_id'])->first();

        if(!$survey || strtotime($survey->start_date) < strtotime('now'))
            return ['error' => 'survey_expired'];

        $survey->name = $request->get('name');
        $survey->start_date = $request->get('start_date');
        $survey->duration = $request->get('duration');
        $survey->expires_at = date('Y-m-d H:i:s', strtotime('+'.$request->get('duration').' minutes', strtotime($survey->start_date)));
        $survey->groups = isset($input['groups']) ? $input['groups'] : '';
        $survey->nparticipants = count($input['users']);
        $survey->nquestions = count($input['questions']);
        $survey->save();

        Questions::where('survey_id', $input['survey_id'])->delete();
        $res_questions = Questions::createQuestions(['survey_id' => $input['survey_id'], 'questions' => $input['questions']]);
        if(!isset($res_questions['success']))
            return $res_questions;

        Assignees::where('survey_id', $input['survey_id'])->delete();
        $res_assignees = Assignees::createAssignees(['survey_id' => $input['survey_id'], 'users' => $input['users']]);
        if(!isset($res_assignees['success'])) 
            return $res_assignees;

        return ['success' => 1, 'survey' => $survey];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        $survey = Surveys::where('id', $id)
                    ->where('school_id', $request->get('school_id'))
                    ->get();

        if(!$survey)
            return ['error' => 1];

        Surveys::where('id', $id)
            ->where('school_id', $request->get('school_id'))
            ->delete();

        Questions::where('survey_id', $id)->delete();
        Assignees::where('survey_id', $id)->delete();

        return ['success' => 1];
    }

    public function list(Request $request)
    {
        $surveys =          Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('school_id', $request->get('school_id'))
                                ->select('surveys.id', 'surveys.name', 'surveys.created_by_name', 'start_date', 'surveys.nparticipants as participants')
                                ->groupBy('surveys.id')
                                ->orderBy('surveys.id', 'desc')
                                ->get();

        $total_assignees =  Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('school_id', $request->get('school_id'))
                                ->count('assignees.id');
        
        $surveys_done =     Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('assignees.status', 1)
                                ->where('school_id', $request->get('school_id'))
                                ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                ->count('*');

        $surveys_canceled = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('assignees.status', -1)
                                ->where('school_id', $request->get('school_id'))
                                ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                ->count('*');

        $surveys_expired =  Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('assignees.status', 0)
                                ->where('school_id', $request->get('school_id'))
                                ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                ->count('*');

        return [
                    'success' => 1, 
                    'data' => [
                                'surveys' => $surveys, 
                                'total' => $surveys->count(), 
                                'total_assignees' => $total_assignees, 
                                'surveys_done' => $surveys_done, 
                                'surveys_canceled' => $surveys_canceled, 
                                'surveys_expired' => $surveys_expired
                            ]
                ];
    }

    public function deleteQuestion(Request $request, $id)
    {
        $question = Questions::join('surveys', 'surveys.id', 'questions.survey_id')
                        ->where('questions.id', $id)
                        ->where('surveys.school_id', $request->get('school_id'))
                        ->select('surveys.start_date as date', 'survey_id')
                        ->first();

        if(!$question || strtotime($question->date) < strtotime('now'))
            return ['error' => 1];

        $survey = Surveys::where('id', $question->survey_id)->first();
        $nquestions = $survey->nquestioons;
        $survey->nquestions = $nquestions -1;
        $survey->save();

        Questions::where('id', $id)->delete();

        return ['success' => 1];
    }

    public function removeParticipant(Request $request)
    {
        $assignee = Assignees::join('surveys', 'surveys.id', 'assignees.survey_id')
                        ->where('assignees.user_id', $request->get('participant_id'))
                        ->where('surveys.id', $request->get('survey_id'))
                        ->select('surveys.start_date as date')
                        ->first();

        if(!$assignee || strtotime($assignee->date) < strtotime('now'))
            return ['error' => 1];

        $survey = Surveys::where('id', $request->get('survey_id'))->first();
        $nparticipants = $survey->nparticipants;
        $survey->nparticipants = $nparticipants -1;
        $survey->save();

        Assignees::where('user_id', $request->get('participant_id'))->where('survey_id', $request->get('survey_id'))->delete();
        
        return ['success' => 1];
    }

    public function showForUser(Request $request, $id)
    {
        if($request->get('role') == 'student')
            $surveys = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                            ->where('assignees.user_id', $id)
                            ->where('surveys.start_date', '<=', date('Y-m-d H:i:s'))
                            ->select('surveys.id', 'name', 'created_by_name', 'start_date', 'duration', 'nquestions as questions', 'nparticipants as participants', 'assignees.status as answered')
                            ->get()->toArray();
        else
            $surveys = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                            ->where('assignees.user_id', $id)
                            ->select('surveys.id', 'name', 'created_by_name', 'start_date', 'duration', 'nquestions as questions', 'nparticipants as participants', 'assignees.status as answered')
                            ->get()->toArray();

        return ['success' => 1, 'data' => ['surveys' => $surveys]];
    }

    public function cancelSurvey(Request $request, $id)
    {
        $survey = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                        ->where('surveys.id', $id)
                        ->where('assignees.user_id', $request->get('user_id'))
                        ->where('surveys.school_id', $request->get('school_id'))
                        ->first();

        if(!$survey)
            return ['error' => 1];

        $answers = Answers::where('survey_id', $id)
                        ->where('user_id', $request->get('user_id'))
                        ->get();

        $assignee = Assignees::where('survey_id', $id)
                        ->where('user_id', $request->get('user_id'))->first();
        $assignee->status = -1;
        $assignee->save();
                        
        
        return ['success' => 1];
    }

    public function questions(Request $request, $id)
    {
        $active = Assignees::where('survey_id', $id)->where('user_id', $request->get('user_id'))->first();

        if(!$active) 
            return ['error' => 'not_assigned'];

        if($active->status == 1)
            return ['success' => 'completed'];

        if($active->status == -1)
            return ['success' => 'cancelled'];

        $survey = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                        ->where('surveys.id', $id)
                        ->where('assignees.user_id', $request->get('user_id'))
                        ->where('surveys.school_id', $request->get('school_id'))
                        ->select('surveys.id', 'surveys.name', 'surveys.duration', 'surveys.start_date')
                        ->first();

        if(!$survey)
            return ['error' => 1];
        
        $questions = Questions::join('surveys', 'surveys.id', 'questions.survey_id')
                        ->where('surveys.id', $id)
                        ->where('start_date', '<=', date('Y-m-d H:i:59'))
                        ->where('expires_at', '>=',  date('Y-m-d H:i:59'))
                        ->select('questions.id', 'questions.question', 'questions.answer_type')
                        ->get()->toArray();

        if(count('questions') > 0)
            return ['success' => 1, 'data' => ['questions' => $questions, 'survey_data' => $survey]];
 
        return ['error' => 'expired'];
    }

    public function saveAnswers(Request $request, $id)
    {
        $survey = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                        ->where('surveys.id', $id)
                        ->where('assignees.user_id', $request->get('user_id'))
                        ->where('surveys.school_id', $request->get('school_id'))
                        ->where('assignees.status', 0)
                        ->first();
        
        if(!$survey)
            return ['error' => 1];

        $input = $request->all();
        $input['survey_id'] = $id;
        $result = Answers::createAnswers($input);

        if(!isset($result['success']))
            return $result;

        Assignees::where('user_id', $request->get('user_id'))
            ->where('survey_id', $id)
            ->update(['status' => 1]);

        return ['success' => 1];
    }

    public function answers(Request $request, $id)
    {
        $questions = Questions::where('survey_id', $id)->select('question')->orderBy('id', 'asc')->get()->toArray();
    
        $assignees = Assignees::where('survey_id', $id)->select('user_id', 'status')->get()->toArray();

        foreach($assignees as $key => $user) {
            if($user['status'] == 1) {
                $answers = Answers::join('questions', 'questions.id', 'answers.question_id')
                                ->where('answers.survey_id', $id)
                                ->where('answers.user_id', $user['user_id'])
                                ->select('answers.answer', 'questions.answer_type')
                                ->orderBy('answers.question_id', 'asc')
                                ->get()->toArray();

                $avg = Answers::join('questions', 'questions.id', 'answers.question_id')
                                ->where('answers.survey_id', $id)
                                ->where('answers.user_id', $user['user_id'])
                                ->whereIn('questions.answer_type', [1, 2, 3])
                                ->avg('answer');
                $assignees[$key]['questions'] = $answers;
                $assignees[$key]['questions']['avg'] = number_format($avg, 2);
            } else
                $assignees[$key]['questions'] = [];
        }

        $ids = Assignees::where('survey_id', $id)->pluck('user_id')->toArray();
        
        return ['success' => 1, 'questions' => $questions, 'answers' => $assignees, 'ids' => $ids];
    }

    public function getForUser($id)
    {
        $surveys = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                        ->where('assignees.user_id', $id)
                        ->where('assignees.status', 1)
                        ->select('surveys.id', 'surveys.name', 'surveys.start_date', 'surveys.nparticipants as nstudents')
                        ->get()->toArray();

        $surveysArr = []; $i=0;

        foreach($surveys as $survey) {
            $surveysArr[] = $survey;
            $surveysArr[$i]['nleaders'] = 0;
            $i++;
        }

        return ['success' => 1, 'data' => ['surveys' => $surveysArr, 'answered_surveys' => count($surveysArr)]];
    }

    public function nextSurvey(Request $request)
    {
        $survey = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                    ->where('assignees.user_id', $request->get('user_id'))
                    ->where('assignees.status', 0)
                    ->where('surveys.start_date', '<=', date('Y-m-d H:i:s'))
                    ->where('surveys.expires_at', '>=',  date('Y-m-d H:i:s'))
                    ->orderBy('surveys.start_date', 'asc')
                    ->select('surveys.id')
                    ->first();

        if($survey)
            return ['success' => 1, 'data' => ['survey_id' => $survey->id]];

        return ['success' => 1, 'data' => []];

    }

    public function lastSurvey($school_id)
    {
        $survey = Surveys::where('start_date', '<=', date('Y-m-d H:i:s'))->where('school_id', $school_id)->orderBy('start_date', 'desc')->first();

        if($survey) {
            $id = $survey->id;
            $total_assignees =  Assignees::where('survey_id', $id)->count(); 

            $surveys_done = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                ->where('surveys.id', $id)
                                ->where('assignees.status', 1)
                                ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                ->count();
            
            $surveys_canceled = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                    ->where('surveys.id', $id)
                                    ->where('assignees.status', -1)
                                    ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                    ->count();

            $surveys_expired =  Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                                    ->where('surveys.id', $id)
                                    ->where('assignees.status', 0)
                                    ->where('surveys.expires_at', '<=', date('Y-m-d H:i:s'))
                                    ->count();

            $questions = Questions::where('questions.survey_id', $id)->count();

            return [
                'success' => 1,
                'data' => [
                    'survey' => $survey,
                    'total_participants' => $total_assignees, 
                    'surveys_done' => $surveys_done, 
                    'surveys_canceled' => $surveys_canceled, 
                    'surveys_expired' => $surveys_expired,
                    'questions' => $questions
                ]
            ];
        }

        return ['success' => 1, 'data' => []];

    }

    public function dashboardDetails($user_id)
    {
        $count_surveys = Surveys::join('assignees', 'assignees.survey_id', 'surveys.id')
                            ->where('assignees.user_id', $user_id)
                            ->where('assignees.status', 1)
                            ->count();

        return ['success' => 1, 'count_surveys' => $count_surveys];
    }

    public function info(Request $request, $id)
    {
        $survey = Surveys::where('id', $id)
                    ->where('school_id', $request->get('school_id'))
                    ->select('name', 'duration', 'groups')
                    ->first();

        if(!$survey)
            return ['error' => 'Survey does not exist'];

        $questions = Questions::where('survey_id', $id)
                        ->select('question as qst', 'answer_type')
                        ->get();

        $assignees = [];
        if($survey->groups == '')
            $assignees = Assignees::where('survey_id', $id)->pluck('user_id');

        return ['success' => 1, 'data' => ['survey' => $survey, 'questions' => $questions, 'assignees' => $assignees]];
    }

    public function deletedGroup(Request $request)
    {
        $group_id = $request->get('group_id');

        $surveys = Surveys::where('groups', 'like', '%,'.$group_id.',%')
                        ->where('start_date', '>', date('Y-m-d H:i:s'))
                        ->get()->toArray();

        if(count($surveys) > 0) {
            foreach($surveys as $survey) {
                $news = \DB::table("tutella-groups.group_members")->whereIn('group_id', explode(',', $survey['groups']))->distinct('user_id')->pluck('user_id')->toArray();
                Assignees::where('survey_id', $survey['id'])->delete();

                if($request->get('role') == 'leader' && !in_array($request->get('user_id'), $news))
                    $news[] = (int)$request->get('user_id');

                $res_assignees = Assignees::createAssignees(['survey_id' => $survey['id'], 'users' => $news]);
                $survey = Surveys::where('id', $survey['id'])->first();
                $survey->nparticipants = count($news);
                $groups = $survey->groups;
                $survey->groups = str_replace(','.$request->get('group_id').',', ',', $groups);
                $survey->save();
            }
        }
        return ['success' => 1];
    }

    public function updatedGroup(Request $request)
    {
        $group_id = $request->get('group_id');

        $surveys = Surveys::where('groups', 'like', '%,'.$group_id.',%')
                        ->where('start_date', '>', date('Y-m-d H:i:s'))
                        ->get()->toArray();

        if(count($surveys) > 0) {
            foreach($surveys as $survey) {
                $news = \DB::table("tutella-groups.group_members")->whereIn('group_id', explode(',', $survey['groups']))->distinct('user_id')->pluck('user_id')->toArray();
                Assignees::where('survey_id', $survey['id'])->delete();

                if($request->get('role') == 'leader' && !in_array($request->get('user_id'), $news))
                    $news[] = (int)$request->get('user_id');

                $res_assignees = Assignees::createAssignees(['survey_id' => $survey['id'], 'users' => $news]);
                $survey = Surveys::where('id', $survey['id'])->first();
                $survey->nparticipants = count($news);
                $survey->save();
            }
        }
        return ['success' => 1];
    }

    public function copy(Request $request, $id)
    {
        $survey = Surveys::where('id', $id)->select('name', 'duration')->first();

        if(!$survey)
            return ['error' => 1];

        $questions = Questions::where('survey_id', $id)->get();

        return ['survey' => $survey, 'questions' => $questions];
    }

    public function removeAssignee($user_id)
    {
        Assignees::where('user_id', $user_id)->delete();
        
        return ['success' => 1];
    }
}
