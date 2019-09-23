<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Groups\SaveGroupRequest;
use App\Http\Requests\Groups\UpdateGroupRequest;
use App\Helpers\CurlHelper;
use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use DB;
use App\Http\Controllers\SurveysController;

class GroupsController extends Controller
{

    protected $surveys;
    public function __construct(SurveysController $surveys)
    {
       $this->surveys = $surveys;
    }

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
     * @SWG\Post(path="/groups/store",
     *   summary="Save group details",
     *   description="Save group details",
     *   operationId="createGroup",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="group_name",
     *     description="Group name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="students",
     *     description="List of student ids in an array",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="leaders",
     *     description="List of leader ids in an array",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> ['group_id' => '', 'group_name' => '']]")
    * )
    */
    public function store(SaveGroupRequest $request)
    {
        if(count($request->get('students')) + count($request->get('leaders')) < 2)
            return response()->json()->setStatusCode(484, 'error_invalid_input');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            
            $input['school_id'] = $user['user']->school_id;
            $input['created_by'] = $user['user']->user_id;

            $groups_url     = env('GROUPS_URL_API').'groups/store';
            $response       = CurlHelper::curlPost($groups_url,$input);

            if(isset($response->success)) {
                return response()->json(['success' =>1, 'data'=> ['group_id' => $response->data->group->id, 'group_name' => $response->data->group->name]])->setStatusCode(200, 'success_group_created');
            }
            
            return response()->json($response)->setStatusCode(485, 'error_group_create');
        }
        
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/groups/{id}",
     *   summary="Show group details",
     *   description="Show group details",
     *   operationId="showGroupDetails",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Response(response="200", description="['group_data' => ['group_name' => '', 'members' => [], 'users' => []]]")
     * )
    */
    public function show(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $groups_url     = env('GROUPS_URL_API').'groups/' . $id;
            $response       = CurlHelper::curlGet($groups_url);
            
            if(isset($response->success) && $response->data->group->school_id == $user['user']->school_id) {
                $not_members = User::join('roles', 'roles.name', 'users.role')
                            ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role', 'roles.display_name as role_display_name')
                            ->where('users.deleted_at', '=', NULL)
                            ->where('users.role', '!=', 'super_admin')
                            ->where('users.role', '!=', 'school_admin')
                            ->where('users.school_id', $user['user']->school_id)
                            ->whereNotIn('user_id', $response->data->members)
                            ->where(function($query) {
                                $query->where('users.active', '1');
                                $query->orWhere('users.pending', '1');
                            })
                            ->get()->toArray();

                $members = User::join('roles', 'roles.name', 'users.role')
                            ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.role', 'roles.display_name as role_display_name')
                            ->where('users.deleted_at', '=', NULL)
                            ->where('users.role', '!=', 'super_admin')
                            ->where('users.role', '!=', 'school_admin')
                            ->where('users.school_id', $user['user']->school_id)
                            ->whereIn('user_id', $response->data->members)
                            ->get()->toArray();

                $group = [
                    'group_name' => $response->data->group->name,
                    'created_at' => $response->data->group->created_at,
                    'group_members' => $members,
                    'users' => $not_members
                ];

                return response()->json(['group_data' => $group])->setStatusCode(200, 'success');
            }

            return response()->json()->setStatusCode(488, 'error_group_not_found');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/groups/update",
     *   summary="Update group details",
     *   description="Update group details",
     *   operationId="updateGroupDetails",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="group_id",
     *     description="Group ID",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="group_name",
     *     description="Group name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="students",
     *     description="List of student ids in an array",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="leaders",
     *     description="List of leader ids in an array",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function update(UpdateGroupRequest $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $groups_url     = env('GROUPS_URL_API').'groups/update';
            $response       = CurlHelper::curlPost($groups_url,$input);

            if(isset($response->success)) {
                $this->surveys->changeAssignees($request->get('group_id'), $user['user']->role, $user['user']->user_id);
                return response()->json(['success' => 1])->setStatusCode(200, 'success_group_updated');
            }
            
            return response()->json($response)->setStatusCode(485, 'error_group_update');
        }
        
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/groups/list",
     *   summary="Show all groups.",
     *   description="Show all groups.",
     *   operationId="shwoGroupsTable",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Response(response="200", description="[groups_list]")
     * )
    */
    public function list(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $input = $request->all();
            $input['school_id'] = $user['user']->school_id;
            $input['user_id'] = $user['user']->user_id;
            $input['user_role'] = $user['user']->role;
            $groups_url     = env('GROUPS_URL_API').'groups/list';
            $response       = CurlHelper::curlGet($groups_url, $input);

            if(isset($response->success)) {
                return response()->json($response->data->groups);
            }
            
            return response()->json($response)->setStatusCode(480, 'error_fetching_data');
        }
        
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Delete(path="/groups/{id}",
     *   summary="Delete group",
     *   description="Delete group",
     *   operationId="deleteGroup",
     *   produces={"application/json"},
     *   tags={"Groups"},
    *   @SWG\Response(response="200", description="['success' =>1]")
    * )
    */
    public function delete(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if($user['user']->role == 'leader')
            $input['leader_id'] = $user['user']->user_id;
        if(isset($user['success'])) {
            $input['group_id'] = $id;
            $groups_url     = env('GROUPS_URL_API').'groups/delete';
            $response       = CurlHelper::curlPost($groups_url,$input);

            if(isset($response->success)) {
                $this->surveys->changeAssigneesDelete($id, $user['user']->role, $user['user']->user_id);
                return response()->json(['success' => 1])->setStatusCode(200, 'success_group_deleted');
            }
            
            return response()->json($response)->setStatusCode(487, 'error_group_delete');
        }
        
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/groups/{user_id}/get",
     *   summary="Show all groups for user.",
     *   description="Show all groups for user.",
     *   operationId="shwoGroupsUser",
     *   produces={"application/json"},
     *   tags={"Groups"},
     *   @SWG\Response(response="200", description="[groups_list]")
     * )
    */
    public function showForUser(Request $request, $user_id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $userCheck = User::where('user_id', $user_id)->where('school_id', $user['user']->school_id)->first();
            
            if($userCheck) {
                $groups_url     = env('GROUPS_URL_API').'groups/'.$user_id.'/show';
                $response       = CurlHelper::curlGet($groups_url);
                
                if(isset($response->success)) {
                    return response()->json(['groups' => $response->groups])->setStatusCode(200, 'success');
                }
                
                return response()->json()->setStatusCode(463, 'error_user_not_found');
            }

            return response()->json()->setStatusCode(474, 'error_not_allowed');
        }
    }

    public function addUserToGroups($user_id, $groups, $role)
    {
        $userCheck = User::where('user_id', $user_id)->first();
        
        if($userCheck) {
            $groups_url     = env('GROUPS_URL_API').'groups/'.$user_id.'/add';
            $response       = CurlHelper::curlPost($groups_url, ['user_id' => $user_id, 'groups' => $groups, 'role' => $role]);

            if(isset($response->success)) {
                return response()->json()->setStatusCode(200, 'success');
            }
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

}
