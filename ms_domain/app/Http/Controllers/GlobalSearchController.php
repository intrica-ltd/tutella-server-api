<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Groups\SaveGroupRequest;
use App\Http\Requests\Groups\UpdateGroupRequest;
use App\Helpers\CurlHelper;
use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use DB;

class GlobalSearchController extends Controller
{

    /**
     * @SWG\Get(path="/globalSearch/{id}",
     *   summary="Global search for student.",
     *   description="Global search for student.",
     *   operationId="globalSearch",
     *   produces={"application/json"},
     *   tags={"Global search"},
     *   @SWG\Response(response="200", description="['user': '', 'documents': '', 'total_documents': '', 'surveys': '', 'answered_surveys': '', 'groups': '', 'total_groups': '']")
     * )
    */
    public function find(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            
            $find_user = User::where('user_id', $id)
                            ->where('school_id', $user['user']->school_id)
                            ->where('role', 'student')
                            ->select('first_name', 'email', 'last_name', 'phone', 'created_at', 'image_id', 'enrollment_code', 'invited_by_name')
                            ->first();

            if(!$find_user)
                return response()->json()->setStatusCode(463, 'error_user_not_found');

            $result = [];
            $result['user'] = $find_user;

            $documents_url = env('DOCUMENTS_URL_API').'documents/'.$id.'/myPhotos';
            $response_documents = CurlHelper::curlGet($documents_url);

            if(!isset($response_documents->success)) {
                $result['documents'] = [];
            } else {
                $result['documents'] = $response_documents->documents;
                $result['total_documents'] = $response_documents->total;
            }

            $surveys_url = env('SURVEYS_URL_API').$id.'/getForUser';
            $response_surveys = CurlHelper::curlGet($surveys_url);

            if(!isset($response_surveys->success)) {
                $result['surveys'] = [];
            } else {
                $result['surveys'] = $response_surveys->data->surveys;
                $result['answered_surveys'] = $response_surveys->data->answered_surveys;
            }

            $groups_url = env('GROUPS_URL_API').'groups/'.$id.'/getForUser';
            $response_groups = CurlHelper::curlGet($groups_url);

            if(!isset($response_groups->success)) {
                $result['groups'] = [];
            } else {
                $result['groups'] = $response_groups->data->groups;
                $result['total_groups'] = $response_groups->data->total_groups;
            }

            return response()->json($result)->setStatusCode(200, 'success');
            
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/globalSearch/users/{role}",
     *   summary="Get all users in the school [students or leaders, depending on the role - accepted values: 'student' and 'leader'].",
     *   description="Get all users in the school [students or leaders, depending on the role - accepted values: 'student' and 'leader'].",
     *   operationId="globalSearchUsers",
     *   produces={"application/json"},
     *   tags={"Global search"},
     *   @SWG\Response(response="200", description="['users': '']")
     * )
    */
    public function users(Request $request, $role)
    {
        if(!in_array($role, ['student', 'leader']))
            return response()->json()->setStatusCode(456, 'error_user_role_not_found');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $users = User::where('school_id', $user['user']->school_id)
                        ->where('role', $role)
                        ->where('active', 1)
                        ->select('user_id', 'first_name', 'last_name')
                        ->get()->toArray();

            return response()->json(['users' => $users])->setStatusCode(200, 'success');

        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

}
