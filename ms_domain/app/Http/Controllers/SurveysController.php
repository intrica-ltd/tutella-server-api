<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Groups\SaveGroupRequest;
use App\Http\Requests\Groups\UpdateGroupRequest;
use App\Helpers\CurlHelper;
use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use DB;

class SurveysController extends Controller
{
    /**
     * @SWG\Post(path="/surveys/store",
     *   summary="Save survey details",
     *   description="Save survey details",
     *   operationId="createSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="name",
     *     description="Survey name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="duration",
     *     description="Duration of the survey in min.",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="start_date",
     *     description="Start date",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="groups",
     *     description="Assign survey to users from groups (array od group ids).",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="users",
     *     description="Assign survey to users (array od users ids).",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['survey' => '']")
    * )
    */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name'        => 'required',
            'duration'    => 'integer|required',
            'start_date'  => 'date|required',
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }

        if(!$request->has('groups') && !$request->has('users')) {
            $fail =  ['error'=>1, 'errors' => ['Groups or users field is required']];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }
        
        if(!$request->has('questions') || count($request->get('questions')) < 1){
            $fail =  ['error'=>1, 'errors' => ['The survey needs to have at least 1 question.']];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }

        if($request->get('start_date') <= date('Y-m-d H:i:s'))
            return response()->json(['error' => 'start_date is in the past'])->setStatusCode(465, 'error_survey_create');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $input = $request->all();

            if(count($input['groups']) > 0) {
                $groups_url     = env('GROUPS_URL_API').'groups/getUsers';
                $response_groups    = CurlHelper::curlPost($groups_url, ['groups' => $input['groups']]);
                if(isset($response_groups->success)) {
                    $input['users'] = $response_groups->users;
                    $input['groups'] = ','.implode(',', $input['groups']).',';
                }
            } else $input['groups'] = '';
            
            if($request->has('groups') && count($input['users']) > 0) {
                $input['school_id'] = $user['user']->school_id;
                $input['created_by'] = $user['user']->user_id;
                $input['created_by_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
                if($user['user']->role == 'leader' && !in_array($user['user']->user_id, $input['users']))
                    $input['users'][] = $user['user']->user_id;

                $surveys_url     = env('SURVEYS_URL_API').'store';
                $response        = CurlHelper::curlPost($surveys_url, $input);

                if(isset($response->success)) {
                    return response()->json(['survey' => $response->survey])->setStatusCode(200, 'success');
                }

                return response()->json()->setStatusCode(465, 'error_survey_create');
            }

            return response()->json()->setStatusCode(466, 'error_survey_create_no_users_assigned');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }


    /**
     * @SWG\Get(path="/surveys/{id}/show",
     *   summary="Show survay details.",
     *   description="Show survay details.",
     *   operationId="editSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Response(response="200", description="['survey': '', 'total_questions': '', 'participants': '', 'surveys_done': '', 'surveys_canceled': '', 'surveys_expired': '', 'questions': '']")
     * )
    */
    public function show(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['school_id'] = $user['user']->school_id;
            $input['role'] = $user['user']->role;
            $input['user_id'] = $user['user']->user_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/show';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                if($input['role'] != 'student')
                    $response->data->participants = User::whereIn('user_id', $response->data->participants)->select('user_id', 'first_name', 'last_name', 'role')->get()->toArray();
                return response()->json($response->data)->setStatusCode(200, 'success');
            }

            return response()->json()->setStatusCode(467, 'error_survey_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/surveys/update",
     *   summary="Update survey details",
     *   description="Update survey details",
     *   operationId="UpdateSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="survey_id",
     *     description="Survey id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="name",
     *     description="Survey name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="duration",
     *     description="Duration of the survey in min.",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="start_date",
     *     description="Start date",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['survey' => '']")
    * )
    */
    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'survey_id'   => 'required',
            'name'        => 'required',
            'duration'    => 'integer|required',
            'start_date'  => 'date|required',
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }

        if(!$request->has('groups') && !$request->has('users')) {
            $fail =  ['error'=>1, 'errors' => ['Groups or users field is required']];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }
        
        if(!$request->has('questions') || count($request->get('questions')) < 1){
            $fail =  ['error'=>1, 'errors' => ['The survey needs to have at least 1 question.']];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }

        if($request->get('start_date') <= date('Y-m-d H:i:s'))
            return response()->json(['error' => 'start_date is in the past'])->setStatusCode(465, 'error_survey_create');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $input = $request->all();

            if(count($input['groups']) > 0) {
                $groups_url     = env('GROUPS_URL_API').'groups/getUsers';
                $response_groups    = CurlHelper::curlPost($groups_url, ['groups' => $input['groups']]);
                if(isset($response_groups->success)) {
                    $input['users'] = $response_groups->users;
                    $input['groups'] = ','.implode(',', $input['groups']).',';
                }
            } else $input['groups'] = '';
            
            if($request->has('groups') && count($input['users']) > 0) {
                $input['school_id'] = $user['user']->school_id;
                $input['created_by'] = $user['user']->user_id;
                $input['created_by_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
                if($user['user']->role == 'leader' && !in_array($user['user']->user_id, $input['users']))
                    $input['users'][] = $user['user']->user_id;

                $surveys_url     = env('SURVEYS_URL_API').'update';
                $response        = CurlHelper::curlPost($surveys_url, $input);

                if(isset($response->success))
                    return response()->json(['survey' => $response->survey])->setStatusCode(200, 'success');

                return response()->json($response)->setStatusCode(465, 'error_survey_update');
            }

            return response()->json()->setStatusCode(466, 'error_survey_create_no_users_assigned');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Delete(path="/surveys/{id}",
     *   summary="Delete survey",
     *   description="Delete survey",
     *   operationId="deleteSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function delete(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/delete';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json(['success' => 1])->setStatusCode(200, 'success_survey_deleted');
            }
            
            return response()->json()->setStatusCode(467, 'error_survey_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/list",
     *   summary="Show all surveys.",
     *   description="Show all surveys.",
     *   operationId="shwoSurveysTable",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Response(response="200", description="[surveys_list]")
     * )
    */
    public function list(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').'list';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json($response->data)->setStatusCode(200, 'success');
            }
            
            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }
        
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Delete(path="/surveys/{id}/question",
     *   summary="Delete question",
     *   description="Delete question",
     *   operationId="deletequestion",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function deleteQuestion(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/deleteQuestion';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json(['success' => 1])->setStatusCode(200, 'success_question_deleted');
            }
            
            return response()->json()->setStatusCode(452, 'error_question_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/surveys/removeParticipant",
     *   summary="Remove participant",
     *   description="Remove participant",
     *   operationId="RemoveParticipant",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="survey_id",
     *     description="Survey id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="participant_id",
     *     description="User id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function removeParticipant(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'survey_id'        => 'required',
            'participant_id'   => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'error_survey_create');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').'removeParticipant';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json(['success' => 1])->setStatusCode(200, 'success_participant_removed');
            }
            
            return response()->json()->setStatusCode(453, 'error_participant_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/mySurveys",
     *   summary="[LEADERS] Get scheduled surveys for loggedin user.",
     *   description="[LEADERS] Get scheduled surveys for loggedin user.",
     *   operationId="showForUser",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['surveys' => '']")
    * )
    */
    public function showForUser(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['role'] = $user['user']->role;
            $surveys_url    = env('SURVEYS_URL_API').$user['user']->user_id.'/showForUser';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json($response->data)->setStatusCode(200, 'success');
            }
            
            return response()->json()->setStatusCode(453, 'error_participant_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/surveys/{survey_id}/cancel",
     *   summary="[STUDENTS] Cancel survey.",
     *   description="[STUDENTS] Cancel survey.",
     *   operationId="cancelForUser",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function cancelSurvey(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['user_id'] = $user['user']->user_id;
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/cancelSurvey';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json(['success' => 1])->setStatusCode(200, 'success_survey_canceled');
            }
            
            return response()->json($response)->setStatusCode(454, 'error_survey_canceled');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/{survey_id}/questions",
     *   summary="[STUDENTS] Get questions to start survey.",
     *   description="[STUDENTS] Get questions to start survey.",
     *   operationId="questions",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['questions' => '']")
    * )
    */
    public function questions(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['user_id'] = $user['user']->user_id;
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/questions';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                if($response->success != 1)
                    return response()->json($response)->setStatusCode(200, 'success');

                $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $user['user']->school_id;
                $response_school = CurlHelper::curlGet($url_school);
                if(isset($response_school->success))
                    $response->data->school_name = $response_school->data->school->school_name;

                return response()->json($response->data)->setStatusCode(200, 'success');
            }

            if(isset($response->error) && $response->error == 'expired')
                return response()->json()->setStatusCode(455, 'error_survey_expired');
    
            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/surveys/{survey_id}/saveAnswers",
     *   summary="[STUDENT] Save answers for survey.",
     *   description="[STUDENT] Save answers for survey.",
     *   operationId="saveAnswers",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="answers",
     *     description="Array of answers for the servay. Ex. 'answers' => [['question_id' => 1, 'answer' => 5], ['question_id' => 2, 'answer' => 5]]",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1]")
    * )
    */
    public function saveAnswers(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['user_id'] = $user['user']->user_id;
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/saveAnswers';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success)) {
                return response()->json(['success' => 1])->setStatusCode(200, 'success');
            }

            if(isset($response->error) && $response->error == 'expired')
                return response()->json()->setStatusCode(455, 'error_survey_expired');
    
            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/{survey_id}/answers",
     *   summary="Get answers for survey.",
     *   description="Get answers for survey.",
     *   operationId="answers",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="[]")
    * )
    */
    public function answers(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['user_id'] = $user['user']->user_id;
            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').$id.'/answers';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                $users = User::whereIn('user_id', $response->ids)->select( DB::raw("CONCAT(first_name, ' ', last_name) AS name"), 'user_id')->pluck('name', 'user_id')->toArray();

                foreach($response->answers as $user) {
                    $user->name = $users[$user->user_id];
                }

                return response()->json(['questions' => $response->questions, 'answers' => $response->answers])->setStatusCode(200, 'success');
            }
    
            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/nextSurvey",
     *   summary="Get next scheduled survey.",
     *   description="Get scheduled survey.",
     *   operationId="nextSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['survey_id' => '']")
    * )
    */
    public function nextSurvey(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['user_id'] = $user['user']->user_id; 
            $surveys_url    = env('SURVEYS_URL_API').'nextSurvey';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success))
                return response()->json($response->data)->setStatusCode(200, 'success');

            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/surveys/{id}/info",
     *   summary="Get survey info to copy.",
     *   description="Get survey info to copy.",
     *   operationId="infoSurvey",
     *   produces={"application/json"},
     *   tags={"Surveys"},
    *   @SWG\Response(response="200", description="['survey' => '', 'questions' => '']")
    * )
    */
    public function info(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['school_id'] = $user['user']->school_id; 
            $surveys_url    = env('SURVEYS_URL_API').$id.'/info';
            $response       = CurlHelper::curlGet($surveys_url, $input);

            if(isset($response->success)) {
                if($response->data->survey->groups == '') {
                    $assigned_users = User::join('roles', 'roles.name', 'users.role')
                                        ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role', 'roles.display_name as role_display_name')
                                        ->whereIn('user_id', $response->data->assignees)
                                        ->where('school_id', $user['user']->school_id)
                                        ->get();

                    $users = User::join('roles', 'roles.name', 'users.role')
                                ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role', 'roles.display_name as role_display_name')
                                ->whereNotIn('user_id', $response->data->assignees)
                                ->where('users.deleted_at', '=', NULL)
                                ->where('users.role', '!=', 'super_admin')
                                ->where('users.role', '!=', 'school_admin')
                                ->where('users.school_id', $user['user']->school_id)
                                ->where(function($query){
                                    $query->where('users.active', 1);
                                    $query->orWhere('users.pending', 1);
                                })
                                ->get();
                    
                    return response()->json(['survey' => $response->data->survey, 'questions' => $response->data->questions, 'users' => $users, 'assigned_users' => $assigned_users])->setStatusCode(200, 'success');
                } else {                
                    $groups_url     = env('GROUPS_URL_API').'groups/getGroupData';
                    $response_groups    = CurlHelper::curlGet($groups_url, ['user_id' => $user['user']->user_id, 'user_role' => $user['user']->role, 'group_ids' => $response->data->survey->groups, 'school_id' =>$user['user']->school_id]);
                    
                    return response()->json(['survey' => $response->data->survey, 'questions' => $response->data->questions, 'groups' => $response_groups->data->groups, 'assigned_groups' => $response_groups->data->assigned_groups])->setStatusCode(200, 'success');
                }
            
            }

            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function changeAssignees($id, $role, $user_id)
    {
        $input['group_id'] = $id; 
        $input['role'] = $role; 
        $input['user_id'] = $user_id; 
        $surveys_url    = env('SURVEYS_URL_API').'updatedGroup';
        $response       = CurlHelper::curlPost($surveys_url, $input);

        return $response;
    }

    public function changeAssigneesDelete($id, $role, $user_id)
    {
        $input['group_id'] = $id; 
        $input['role'] = $role; 
        $input['user_id'] = $user_id; 
        $surveys_url    = env('SURVEYS_URL_API').'deletedGroup';
        $response       = CurlHelper::curlPost($surveys_url, $input);

        return $response;
    }

    /**
     * @SWG\Post(path="/surveys/{survey_id}/copy",
     *   summary="Copy survey details.",
     *   description="Copy survey details.",
     *   operationId="copySurveys",
     *   produces={"application/json"},
     *   tags={"Surveys"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="answers",
     *     description="Array of answers for the servay. Ex. 'answers' => [['question_id' => 1, 'answer' => 5], ['question_id' => 2, 'answer' => 5]]",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1]")
    * )
    */
    public function copy(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input['school_id'] = $user['user']->school_id; 
            $surveys_url    = env('SURVEYS_URL_API').$id.'/copy';
            $response       = CurlHelper::curlPost($surveys_url, $input);

            if(isset($response->success))
                return response()->json($response->data)->setStatusCode(200, 'success');

            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

}
