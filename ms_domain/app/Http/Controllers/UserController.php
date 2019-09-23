<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\InviteUserRequest;
use App\Http\Requests\Users\ReinviteUserRequest;
use App\Http\Requests\Users\UserExistsRequest;
use App\Http\Requests\Users\UpdatePendingRequest;
use App\Models\Role;
use App\Models\SchoolPackage;
use App\Services\ActiveCampaignService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use App\Helpers\CurlHelper;

class UserController extends Controller
{
    protected $enrollmentCode;

    protected $groups;

    /** @var ActiveCampaignService */
    private $activeCampaignService;

    public function __construct(EnrollmentCodeController $enrollmentCode, GroupsController $groups, ActiveCampaignService $activeCampaignService)
    {
       $this->enrollmentCode = $enrollmentCode;
       $this->groups = $groups;
       $this->activeCampaignService = $activeCampaignService;
    }

    public function index()
    {
        //
    }

    /**
     * @SWG\Post(path="/user/invite",
     *   summary="Invite user to system ( send email with enrollment code )",
     *   description="Invite user to system ( send email with enrollment code )",
     *   operationId="inviteUser",
     *   produces={"application/json"},
     *   tags={"User"},
     *   @SWG\Parameter(
      *     in="body",
      *     name="enrollment_code",
      *     description="Enrollment code",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *  @SWG\Parameter(
      *     in="body",
      *     name="first_name",
      *     description="First Name",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="last_name",
      *     description="Last Name",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="email",
      *     description="Email",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="role",
      *     description="Role",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="groups",
      *     description="List of group IDs that the user should be part of",
      *     required=false,
      *     @SWG\Schema(ref="#")
      *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> '']")
     * )
    */    
    public function inviteUser(InviteUserRequest $request)
    {

        if($request->get('role') != 'student' && $request->get('role') != 'leader')
            return response()->json()->setStatusCode(475, 'error_role_not_found');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            if($request->has('email') && $request->get('email') != null && $request->get('email') != '') {
                $checkUser = User::checkIfEmailExsist($request->get('email'));
                if(!isset($checkUser['success']))
                    return response()->json()->setStatusCode(473, 'error_user_exists');
            }

            $checkUser = User::checkIfUsernameExsist($request->get('username'));
            if(!isset($checkUser['success']))
                return response()->json()->setStatusCode(474, 'error_user_exists');

            $old_user = $user['user'];

            $input = $request->all();
            $code = $this->enrollmentCode->generate($request)->getData()->code;
            $input['enrollment_code'] = $code;
            $input['invited_by'] = $user['user']->user_id;
            $input['invited_by_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
            $input['school_id'] = $user['user']->school_id;
            $url = ENV("OAUTH_URL_API")."user/inviteUser";
            $response = CurlHelper::curlPost($url, $input);
            
            if(isset($response->success)) {
                $input['user_id'] = $response->data->user_id;
                $user = User::createUser($input);

                if($request->has('groups'))
                    $this->groups->addUserToGroups($response->data->user_id, $request->get('groups'), $request->get('role'));

                if($request->has('email') && $request->get('email') != null && $request->get('email') != '') {
                    $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $old_user->school_id;
                    $response_school = CurlHelper::curlGet($url_school);
                    $email_data['email'] = $input['email'];
                    $email_data['name'] = $input['first_name'] . ' ' . $input['last_name'];
                    $email_data['school_name'] = $response_school->data->school->school_name;
                    $email_data['enrollment_code'] = str_split($input['enrollment_code']);
                    $email_data['role'] = $input['role'];
                    \Mail::send('emails.enrollment', $email_data, function ($m) use ($email_data) {
                        $m->from('no-reply@tutella.com', '');
                        $m->to($email_data['email'], '')->subject('You’re invited to join Tutella!');
                    });
                }
                
                SchoolPackage::checkPackage($old_user->school_id);

                return response()->json(['enrollment_code' => $code])->setStatusCode(200, 'success_user_invited');
            } else {
                return response()->json($response)->setStatusCode(462, 'error_user_invite');
            }
        } else {
            return response()->json()->setStatusCode(463, 'error_user_not_found');
        }
    }

    /**
     * @SWG\Post(path="/user/reinvite",
     *   summary="Re-invite user to system ( send email with enrollment code )",
     *   description="Re-invite user to system ( send email with enrollment code )",
     *   operationId="reinviteUser",
     *   produces={"application/json"},
     *   tags={"User"},
      *   @SWG\Parameter(
      *     in="body",
      *     name="email",
      *     description="email",
      *     required=true,
      *     @SWG\Schema(ref="#")
      *   ),
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> '']")
     * )
     */
    public function reinviteUser(ReinviteUserRequest $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $old_user = $user['user'];

            $code = $this->enrollmentCode->generate($request)->getData()->code;
            $input = $request->all();
            $input['enrollment_code'] = $code;
            
            $user = User::reinviteUser($input);
            if(!isset($user['error'])) {
                $url = ENV("OAUTH_URL_API")."user/inviteUser";
                $response = CurlHelper::curlPost($url, $input);

                if(isset($response->success)) {
                    if($user->email != '' && $user->email != null) {
                        $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $old_user->school_id;
                        $response_school = CurlHelper::curlGet($url_school);
                        $email_data['email'] = $input['email'];
                        $email_data['name'] = $response->data->first_name . ' ' . $response->data->last_name;
                        $email_data['school_name'] = $response_school->data->school->school_name;
                        $email_data['enrollment_code'] = str_split($input['enrollment_code']);
                        \Mail::send('emails.enrollment', $email_data, function ($m) use ($email_data) {
                            $m->from('no-reply@tutella.com', '');
                            $m->to($email_data['email'], '')->subject('You’re invited to join Tutella!');
                        });
                    }
                
                    return response($response->data)->json()->setStatusCode(200, 'success_user_reinvited');
                }

                return response()->json($response)->setStatusCode(462, 'error_user_invite');
            }
            else
                return response()->json()->setStatusCode(472, 'error_user_registered_or_not_invited');

        } else {
            return response()->json()->setStatusCode(463, 'error_user_not_found');
        }
    }

    /**
     * @SWG\Get(path="/user/{id}",
     *   summary="Show user details.",
     *   description="Show user details.",
     *   operationId="shwoUser",
     *   produces={"application/json"},
     *   tags={"User"},
     *   @SWG\Response(response="200", description="[user_data]")
     * )
    */
    public function show($user_id, Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if($user['user'] == 'student' || $user['user']->role == 'leader') {
            if($user['user']->user_id != $user_id)
                return response()->json()->setStatusCode(474, 'error_not allowed');
        }

        $checkUser = User::checkIfUserExsist($user_id);
        if(isset($checkUser['success']) && $checkUser['user']->school_id == $user['user']->school_id) {
            $user = $checkUser['user'];

            $groups = [];
            $groups_url = env('GROUPS_URL_API').'groups/' . $user_id . '/show';
            $response_groups = CurlHelper::curlGet($groups_url);

            if(isset($response_groups->success))
                $groups = $response_groups->groups;

            $response = [
                    'user_id'           =>  $user['user_id'],
                    'first_name'        =>  $user['first_name'],
                    'last_name'         =>  $user['last_name'],
                    'username'          =>  $user['username'],
                    'email'             =>  $user['email'],
                    'start_date'        =>  $user['start_date'],
                    'active'            =>  $user['active'],
                    'role'              =>  $user['role'],
                    'image'             =>  $user['image'] === '' ? null : $user['image'],
                    'image_id'          =>  $user['image_id'] === '' ? null : $user['image_id'],
                    'enrollment_code'   =>  $user['enrollment_code'],
                    'phone'             =>  $user['phone'],
                    'invited_by'        =>  $user['invited_by_name'],
                    'groups'            =>  $groups
                ];

            $response['role'] = [];
            $roleName = Role::where('name', $user['role'])->first();
            if($roleName) {
                $response['role']['name'] = $roleName['name'];
                $response['role']['display_name'] = $roleName['display_name'];
            }

            $documents_url     = env('DOCUMENTS_URL_API').'documents/'.$user['user_id'].'/dashboardDetails';
            $response_doc   = CurlHelper::curlGet($documents_url);
            
            $photos = 0;
            if(isset($response_doc->success)) {
                $photos = $response_doc->count_documents;
                $response['totalPhotos'] = $photos;
            }

            return response()->json($response)->setStatusCode(200, 'success');

        } else
            return response()->json()->setStatusCode(463, 'error_user_not_found');
    }
    
     /**
      * @SWG\Put(path="/user/{id}",
      *   summary="Edit user data",
      *   description="Update data for user",
      *   operationId="updateUser",
      *   produces={"application/json"},
      *   tags={"User"},
      *   @SWG\Parameter(
      *     in="body",
      *     name="initiator_id",
      *     description="The user that is sending the request",
      *     required=true,
      *    @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="first_name",
      *     description="First Name",
      *     required=true,
      *     @SWG\Schema(ref="#")),
      *   @SWG\Parameter(
      *     in="body",
      *     name="last_name",
      *     description="Last Name",
      *     required=true,
      *    @SWG\Schema(ref="#")),
      *   @SWG\Parameter(
      *     in="body",
      *     name="email",
      *     description="Email",
      *     required=true,
      *    @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="password",
      *     description="With password confirmation",
      *     required=false,
      *    @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Parameter(
      *     in="body",
      *     name="groups",
      *     description="List of group IDs that the user should be part of",
      *     required=false,
      *     @SWG\Schema(ref="#")
      *   ),
      *   @SWG\Response(response="200", description="['success' =>1, 'data'=> []]")
      * )
    */
    public function update(UpdateUserRequest $request, $user_id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $myUser = User::authUser(end($split_token));

        $check = User::checkIfUserExsist($user_id);
        if(isset($check['success']))
        {
            $user = $check['user'];
            $url = ENV("OAUTH_URL_API")."user/" . $user['user_id'];

            $input = $request->all();
            if(!isset($input['email'])) $input['email'] = ''; 
            $res = CurlHelper::curlPut($url, $input);
            if(!isset($res->success))
                return response()->json($res)->setStatusCode(459, 'error_user_update');
            // $input
            if($request->has('email') && $request->get('email') != '' && $request->get('email') != null && $request->get('email') != $user->email) {
                $email_data['email'] = $request->get('email');

                if($myUser['user']->user_id != $user['user_id']) {
                    \Mail::send('emails.changedEmail', $email_data, function ($m) use ($email_data) {
                        $m->from('no-reply@tutella.com', '');
                        $m->to($email_data['email'], '')->subject('Your account email address has been changed!');
                    });
                }
            }

            User::updateUserData($user['user_id'], $request->all());
            
            $updatedUser = User::where('user_id', $user['user_id'])->first();
            $data['first_name'] = $updatedUser->first_name;
            $data['last_name'] = $updatedUser->last_name;
            $data['username'] = $updatedUser->username;
            $data['email'] = $updatedUser->email;
            $data['phone'] = $updatedUser->phone;

            if($request->has('groups'))
                $this->groups->addUserToGroups($user['user_id'], $request->get('groups'), $check['user']->role);

            if($check['user']->first_name != $request->get('first_name') || $check['user']->last_name != $request->get('last_name')) {
                $documents_url      = env('DOCUMENTS_URL_API').'documents/updateOwnerName';
                $response           = CurlHelper::curlPost($documents_url, ['owner_id' => $check['user']->user_id, 'owner_name' => $data['first_name'] . ' ' . $data['last_name']]);
            }
                
            return response()->json(['success' => 1, 'data' => $data])->setStatusCode(200, 'success_user_update');
        } else {
            return response()->json($check)->setStatusCode(463, 'error_user_not_found');
        }
    }

    /**
      * @SWG\Delete(path="/user/{id}/deactivate",
      *   summary="Deactivate user.",
      *   description="Deactivate user.",
      *   operationId="deactivateUser",
      *   produces={"application/json"},
      *   tags={"User"},
      *   @SWG\Response(response="200", description="[user_data]")
      * )
    */
    public function deactivate($user_id)
    {
        $check = User::checkIfUserExsist($user_id);

        if(isset($check['success'])) {
            $user = $check['user'];

            $url = ENV('OAUTH_URL_API') . 'user/' . $user['user_id'] . '/deactivate';
            $response = CurlHelper::curlDelete($url);

            if($response->success == 1) {
                User::where('user_id', $user_id)->update(['active' => 0]);

                UserToken::where('user_id', $user_id)->delete();

                SchoolPackage::checkPackage($user['school_id']);

                return response()->json(['success'=>1])->setStatusCode(200, 'success_user_deactivated');
            } else {
                return response()->json($check)->setStatusCode(463, 'error_user_not_found');
            }
        } else {
             return response()->json($check)->setStatusCode(463, 'error_user_not_found');
        }
    }

     /**
       * @SWG\Put(path="/user/{id}/activate",
       *   summary="Activate user.",
       *   description="Activate user.",
       *   operationId="ActivateUser",
       *   produces={"application/json"},
       *   tags={"User"},
       *   @SWG\Response(response="200", description="[user_data]")
       * )
     */
    public function activate($user_id)
    {
        $check = User::checkIfUserExsist($user_id);

        if(isset($check['success'])) {
            $user = $check['user'];

            $url = ENV('OAUTH_URL_API') . 'user/' . $user_id . '/activate';
            $response = CurlHelper::curlPut($url);

            if($response->success == 1) {
                User::where('user_id', $user_id)->update(['active' => 1]);
                SchoolPackage::checkPackage($user['school_id']);

                return response()->json(['success'=>1])->setStatusCode(200, 'success_user_activated');
            } else {
                return response()->json($check)->setStatusCode(463, 'error_user_not_found');
            }
        } else {
            return response()->json($check)->setStatusCode(463, 'error_user_not_found');
        }
    }
    

    /**
     * @SWG\Get(
     *   path="/user/role",
     *   summary="List all user roles",
     *   tags={"Roles"},
     *   operationId="getRoles",
     *   @SWG\Response(
     *     response=200,
     *     description="['success'=>roles]"
     *   )
     * )
     */
    public function getRoles()
    {
        $roles = Role::all();

        return response()->json($roles)->setStatusCode(200, 'success');
    }


    /**
     * @SWG\POST(
     *   path="/user/exists",
     *   summary="Check if user with given email address exists",
     *   tags={"User"},
     *   operationId="userExists",
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1]")
     * )
     */
    public function exists(UserExistsRequest $request)
    {
        $checkUsername = User::checkIfUsernameExsist($request->get('username'));
        if(!isset($checkUsername['success']))
            return response()->json()->setStatusCode(474, 'error_user_exists');
    
        if($request->has('email')) {
            $checkUser = User::checkIfEmailExsist($request->get('email'));

            if(!isset($checkUser['success']))
                return response()->json()->setStatusCode(473, 'error_user_exists');
        }

        return response()->json(['success'=>1])->setStatusCode(200, 'user_not_found');
    }

    /**
     * @SWG\POST(
     *   path="/user/updatePending",
     *   summary="Check if user with given email address exists",
     *   tags={"User"},
     *   operationId="updatePendingUser",
     *   @SWG\Parameter(
     *     in="body",
     *     name="old_email",
     *     description="Old email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="new_email",
     *     description="New email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="enrollment_code",
     *     description="Enrollment code",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="first_name",
     *     description="First name",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="last_name",
     *     description="Last name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="role",
     *     description="Role",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="phone",
     *     description="Phone",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' =>1]")
     * )
     */
    public function updatePending(UpdatePendingRequest $request)
    {
        $update = User::updatePendingUserData($request->all());

        if(isset($update['success'])) {
            if($request->get('old_email') !== $request->get('new_email')) {
                $input['email'] = $request->get('new_email');
                $input['enrollment_code'] = $request->get('enrollment_code');
                $url = ENV("OAUTH_URL_API")."user/inviteUser";
                $response = CurlHelper::curlPost($url, $input);
            }
            return response()->json(['success'=>1])->setStatusCode(200, 'user_updated');
        } else
            return response()->json()->setStatusCode(463, 'user_not_found');
    }

    /**
     * @SWG\Get(
     *   path="/user/dashboardDetails",
     *   summary="Leader/Students dashboard details",
     *   tags={"User"},
     *   operationId="getDashDetails",
     *   @SWG\Response(
     *     response=200,
     *     description="['success' => 1, 'details' => ['groups' => number_groups, 'photos' => number_photos]]"
     *   )
     * )
     */
    public function dashboardDetails(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $groups_url = env('GROUPS_URL_API').'groups/'.$user['user']->user_id.'/dashboardDetails';
            $response_groups = CurlHelper::curlGet($groups_url);

            $groups = 0;
            if(isset($response_groups->success)) {
                $groups = $response_groups->count_groups;
            }

            $documents_url     = env('DOCUMENTS_URL_API').'documents/'.$user['user']->user_id.'/dashboardDetails';
            $response_doc   = CurlHelper::curlGet($documents_url);
            
            $photos = 0;
            if(isset($response_doc->success)) {
                $photos = $response_doc->count_documents;
            }

            $surveys_url     = env('SURVEYS_URL_API').$user['user']->user_id.'/dashboardDetails';
            $response_surveys   = CurlHelper::curlGet($surveys_url);
            
            $surveys = 0;
            if(isset($response_surveys->success)) {
                $surveys = $response_surveys->count_surveys;
            }

            return response()->json(['success' => 1, 'details' => ['groups' => $groups, 'photos' => $photos, 'surveys' => $surveys]])->setStatusCode(200, 'success');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function firebaseToken(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firebase_token'    => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $user = User::where('user_id', $user['user']->user_id)->first();
            $user->firebase_token = $request->get('firebase_token');
            $user->save();
            return response()->json(['success' => 1])->setStatusCode(200, 'success');
        
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
      * @SWG\Put(path="/user/logout",
      *   summary="Logout user",
      *   description="Logout user",
      *   operationId="logoutUser",
      *   produces={"application/json"},
      *   tags={"Login"},
      *   @SWG\Response(response="200", description="['success' =>1]")
      * )
    */
    public function logout(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $update = User::where('user_id', $user['user']->user_id)->first();
            $update->firebase_token = null;
            $update->save();

            return response()->json(['success' => 1])->setStatusCode(200, 'success');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function welcomeMsg(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $update = User::where('user_id', $user['user']->user_id)->first();
            $update->welcome_msg = 1;
            $update->save();

            return response()->json(['success' => 1])->setStatusCode(200, 'success');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function delete(Request $request, $user_id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $update = User::where('user_id', $user_id)->first();

            if(!$update || $update->pending == 0 || $update->school_id != $user['user']->school_id)
                return response()->json()->setStatusCode(459, 'error_user_not_pending');

            $surveys_url = env('SURVEYS_URL_API').$user_id.'/removeAssignee';
            $response_surveys = CurlHelper::curlPost($surveys_url);

            $groups_url = env('GROUPS_URL_API').$user_id.'/removeMember';
            $response_groups = CurlHelper::curlPost($groups_url);
            
            $url = ENV("OAUTH_URL_API").$user_id."/removeUser";
            $response = CurlHelper::curlPost($url);

            $update = User::where('user_id', $user_id)->delete();

            return response()->json(['success' => 1])->setStatusCode(200, 'success');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }
}