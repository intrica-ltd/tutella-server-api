<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Schools\SaveSchoolRequest;
use App\Helpers\CurlHelper;
use App\Models\User;
use App\Models\SchoolPackage;
use Facebook\Facebook;
use Intervention\Image\ImageManagerStatic as ImageIntervention;
use App\Services\ActiveCampaignService;

class SchoolsController extends Controller
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
     * @SWG\Post(path="/schools/store",
     *   summary="Save school details on first log in",
     *   description="Save school details on first log in",
     *   operationId="createSchool",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="school_name",
     *     description="School name",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address",
     *     description="School address ",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Contact email",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address_lat",
     *     description="School address latitude",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address_lng",
     *     description="School address longitude",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="phone",
     *     description="Contact phone ",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="logo",
     *     description="School logo (path)",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> '']")
    * )
    */    
    public function store(SaveSchoolRequest $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            if($user['user']->school_id != null) 
                return response()->json()->setStatusCode(483, 'error_school_profile_already_created');

            $file = $request->file('logo');

            $input = $request->all();
            if($file && in_array($file->getClientOriginalExtension(), ['png', 'jpg', 'jpeg'])) {

                $file_name = md5($file->getClientOriginalName() . time());
                $full_file_name = $file_name.'.'.$file->getClientOriginalExtension();
                $full_thumbnail_name = $file_name.'_thumb.'.$file->getClientOriginalExtension();

                $input['logo'] = $full_file_name;

                $input_doc = [];
                $input_doc['name'] = $full_file_name;
                $input_doc['name_thumbnail'] = $full_thumbnail_name;
                $input_doc['school_id'] = 0;
                $input_doc['owner_id'] = $user['user']->user_id;
                $input_doc['owner_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
                $input_doc['group_id'] = '';
                $input_doc['type'] = 'school_avatar';
                $input_doc['size'] = round($request->file('logo')->getClientSize(), 2);

                $documents_url      = env('DOCUMENTS_URL_API').'documents/store';
                $response           = CurlHelper::curlPost($documents_url, $input_doc);

                if(isset($response->success)){
                    $file->move(env('FILE_STORAGE').'/', $full_file_name);
                    
                    $image = ImageIntervention::make(env('FILE_STORAGE') . '/' . $full_file_name)->resize(300, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save(env('FILE_STORAGE') . '/thumbnails/' . $full_thumbnail_name);
                    $input['logo_id'] = $response->document->id;
                }
            }

            $schools_url     = env('SCHOOLS_URL_API').'schools/store';
            $input['user_id'] = $user['user']->user_id;
            $response = CurlHelper::curlPost($schools_url, $input);
            if(isset($response->success)) {
                User::updateUserData($user['user']->user_id, ['school_id' => $response->data->school_id]);
            
                if(isset($input['logo_id'])) {
                    $documents_url      = env('DOCUMENTS_URL_API').'documents/updateSchool';
                    $response_doc_update = CurlHelper::curlPost($documents_url, ['doc_id' => $input['logo_id'], 'school_id' => $response->data->school_id]);
                }

                $payment_url = ENV('PAYMENT_API_URL').'billing/store/'.$response->data->school_id.'/1';
                $response_payment = CurlHelper::curlPost($payment_url, []);
                $school_package = new SchoolPackage();
                $school_package->school_id = $response->data->school_id;
                $school_package->package_id = 1;
                $school_package->save();

                $this->activeCampaignService->subscribeUser($user['user']->email);

                return response()->json(['success' =>1, 'data'=> ['school_id' => $response->data->school_id]])->setStatusCode(200, 'success_school_created');
            }
            return response()->json($response)->setStatusCode(476, 'error_school_create');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
        
    }

    /**
     * @SWG\Get(path="/schools/list",
     *   summary="Show all schools.",
     *   description="Show all schools.",
     *   operationId="shwoSchoolsTable",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Response(response="200", description="[schools_list]")
     * )
    */
    public function list()
    {
        $schools_url     = env('SCHOOLS_URL_API').'schools/list';
        $response = CurlHelper::curlGet($schools_url);

        if(isset($response->success)) {

            $schools = $response->data->schools;

            foreach($schools as $school) {
                $school->number_users = User::where('school_id', $school->id)->whereIn('role', ['leader', 'student'])->where('active', 1)->count();
                $package = SchoolPackage::join('billing_packages', 'billing_packages.id', 'school_package.package_id')->where('school_id', $school->id)->first();
                if($package) {
                    $school->package_name = $package->name;
                    $school->package_users = $package->max_users;
                    $school->package_price = $package->price;
                }
            }

            $payments_url = env('PAYMENT_API_URL').'schools/info';
            $response_payments = CurlHelper::curlGet($payments_url);
            if(isset($response_payments->success)) {
                $response->data->revenue = $response_payments->data->revenue;
                $response->data->overdue = $response_payments->data->overdue;
            }
            return response()->json($response->data);
        }
        
        return response()->json($response)->setStatusCode(476, 'error_school_create');
    }

    /**
     * @SWG\Post(path="/schools/activate",
     *   summary="Activate school",
     *   description="Activate school",
     *   operationId="activateSchool",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="school_id",
     *     description="School id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> '']")
    * )
    */
    public function activate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'school_id'      => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(477, 'error_school_activate');
        }

        $check = User::checkIfSchoolExists($request->get('school_id'));

        if(isset($check['success'])) {
            $user = $check['user'];

            $url = ENV('SCHOOLS_URL_API') . 'schools/activate';
            $response_school = CurlHelper::curlPost($url, ["school_id" => $request->get('school_id')]);

            if(isset($response_school->success)) {
                return response()->json(['success'=>1])->setStatusCode(200, 'success_school_activated');
            }
        }
        return response()->json($check)->setStatusCode(477, 'error_school_activate');

    }

    /**
     * @SWG\Post(path="/schools/deactivate",
     *   summary="Deactivate school",
     *   description="Activate school",
     *   operationId="deactivateSchool",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="school_id",
     *     description="School id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' =>1, 'data'=> '']")
    * )
    */
    public function deactivate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'school_id'      => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(478, 'error_school_deactivate');
        }

        $check = User::checkIfSchoolExists($request->get('school_id'));

        if(isset($check['success'])) {
            $user = $check['user'];

            $url = ENV('SCHOOLS_URL_API') . 'schools/deactivate';
            $response_school = CurlHelper::curlPost($url, ["school_id" => $request->get('school_id')]);

            $email_data = ['email' => $user->email];
            if(isset($response_school->success)) {
                \Mail::send('emails.deactivated', $email_data, function ($m) use ($email_data) {
                    $m->from('no-reply@tutella.com', '');
                    $m->to($email_data['email'], '')->subject('Tutella email activation.');
                });

                return response()->json(['success'=>1])->setStatusCode(200, 'success_school_deactivated');
            }
        }
        return response()->json()->setStatusCode(478, 'error_school_deactivate');
    }

    /**
     * @SWG\Get(path="/schools/{id}",
     *   summary="Show school details",
     *   description="Show school details",
     *   operationId="showSchoolDetails",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Response(response="200", description="['school_data' => data]")
     * )
    */
    public function show(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success']) && $user['user']->school_id == $id) {
            $url = ENV('SCHOOLS_URL_API') . 'schools/' . $id;
            $response = CurlHelper::curlGet($url);
            
            if(isset($response->success)) {
                $school = [
                    'school_name' => $response->data->school->school_name,
                    'address' => $response->data->school->address,
                    'email' => $response->data->school->email,
                    'phone' => $response->data->school->phone,
                    'logo' => $response->data->school->logo,
                    'logo_id' => $response->data->school->logo_id
                ];

                $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$id;
                $response_doc   = CurlHelper::curlGet($groups_url);
                
                $users_count = User::where('school_id', $id)->whereIn('role', ['leader', 'student'])->where('active', 1)->count();
                $package = SchoolPackage::join('billing_packages', 'school_package.package_id', 'billing_packages.id')
                            ->where('school_package.school_id', $id)
                            ->get();

                $billing_info = [
                    'users_count' => $users_count,
                    'package_name' => $package[0]->name,
                    'min_users' => $package[0]->min_users,
                    'max_users' => $package[0]->max_users,
                    'price' => $package[0]->price
                ];

                $url_billing = ENV("PAYMENT_API_URL").$id."/totalSchoolOverdue";
                $response_billing = CurlHelper::curlGet($url_billing);
                if(isset($response_billing->success))
                    $billing_info['overdue'] = $response_billing->total_overdue;

                return response()->json(['school_data' => $school, 'billing_info' => $billing_info])->setStatusCode(200, 'success');
            }

            return response()->json()->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/schools/view/{id}",
     *   summary="[SUPER ADMIN] View school details",
     *   description="View school details",
     *   operationId="ViewSchoolDetails",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Response(response="200", description="['school_data' => data]")
     * )
    */
    public function view(Request $request, $id)
    {
        $url = ENV('SCHOOLS_URL_API') . 'schools/' . $id;
        $response = CurlHelper::curlGet($url);

        if(isset($response->success)) {
            $schoolAdmin = User::where('user_id', $response->data->school->user_id)->first();

            $school = [
                'school_name' => $response->data->school->school_name,
                'address' => $response->data->school->address,
                'email' => $response->data->school->email,
                'phone' => $response->data->school->phone,
                'logo' => $response->data->school->logo,
                'logo_id' => $response->data->school->logo_id,
                'created_at' => $response->data->school->created_at,
                'active' => $response->data->school->active
            ];

            $admin = [
                'user_id' => $schoolAdmin->user_id,
                'first_name' => $schoolAdmin->first_name,
                'last_name' => $schoolAdmin->last_name,
                'phone' => $schoolAdmin->phone,
                'email' => $schoolAdmin->email,
                'photo_id' => $schoolAdmin->image_id
            ];

            $result = [
                'school_data' => $school, 
                'school_admin' => $admin
            ];

            $payments_url = env('PAYMENT_API_URL').'school/'.$id;
            $response_payments = CurlHelper::curlGet($payments_url);
            if(isset($response_payments->success)) {
                $result['overdue'] = $response_payments->data->overdue;
                $result['billing_history'] = $response_payments->data->billing_history;
            }

            $users_count = User::where('school_id', $id)->whereIn('role', ['leader', 'student'])->where('active', 1)->count();
            $package = SchoolPackage::join('billing_packages', 'school_package.package_id', 'billing_packages.id')
                        ->where('school_package.school_id', $id)
                        ->get();

            $result['billing_info'] = [
                'users_count' => $users_count,
                'package_name' => $package[0]->name,
                'min_users' => $package[0]->min_users,
                'max_users' => $package[0]->max_users,
                'price' => $package[0]->price
            ];

            $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$id;
            $response_doc   = CurlHelper::curlGet($groups_url);
            
            return response()->json($result)->setStatusCode(200, 'success');
        }
        return response()->json()->setStatusCode(480, 'error_fetching_data');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * @SWG\Post(path="/schools/update",
     *   summary="Update school details",
     *   description="Update school details",
     *   operationId="updateSchoolDetails",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="school_id",
     *     description="School id",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="school_name",
     *     description="School name",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address",
     *     description="School address",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="email",
     *     description="Contact email",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address_lat",
     *     description="School address latitude",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="address_lng",
     *     description="School address longitude",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="phone",
     *     description="Contact phone ",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="logo",
     *     description="School logo (path)",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1, 'school_data' => 'school_data']")
    * )
    */
    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'school_id'      => 'required'
        ]);

        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(479, 'error_school_update');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success']) && $user['user']->school_id == $request->get('school_id')) {
            $url = ENV('SCHOOLS_URL_API') . 'schools/update';
            $response = CurlHelper::curlPost($url, $request->all());

            if(isset($response->success)) {
                $response_school['school_name'] = $response->data->school->school_name;
                $response_school['address'] = $response->data->school->address;
                $response_school['email'] = $response->data->school->email;
                $response_school['address_lat'] = $response->data->school->address_lat;
                $response_school['address_lng'] = $response->data->school->address_lng;
                $response_school['phone'] = $response->data->school->phone;
                $response_school['logo'] = $response->data->school->logo;
                
                return response()->json(['success' => 1, 'school_data' => $response_school])->setStatusCode(200, 'success');
            }
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @SWG\Get(path="/schools/socialConnections",
     *   summary="Get school's social settings",
     *   description="Get school's social settings",
     *   operationId="showSchoolSocial",
     *   produces={"application/json"},
     *   tags={"Schools"},
     *   @SWG\Response(response="200", description="['connected' => [0 or 1], 'fb_page' => 'fb_page']")
     * )
    */
    public function socialConnections(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $url = ENV('SCHOOLS_URL_API') . 'schools/'.$user['user']->school_id.'/socialConnections';
            $response = CurlHelper::curlGet($url);

            if(isset($response->success)) {
                return response()->json($response->data)->setStatusCode(200, 'success');
            }
            return response()->json()->setStatusCode(480, 'error_fetching_data');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function cancelSubscription(Request $request)
    {
	    $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

	    if(isset($user['success'])) {
		    if($user['user']->school_id != null) {
			    User::where('school_id', '=', $user['user']->school_id)
				    ->where('role', '!=', 'school_admin')
				    ->update(['active' => 0]);

			    $url = ENV('SCHOOLS_URL_API') . 'schools/' . $user['user']->school_id . '/destroy';
			    $response = CurlHelper::curlDelete($url, []);

			    if(isset($response->success)) {
				    return response()->json(['success' => 1])->setStatusCode(200, 'success');
			    }

			    return response()->json()->setStatusCode(449, 'error_school_not_found');
		    }
	    }
	    return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

	public function reactivateSubscription(Request $request)
	{
		$split_token  = explode(' ', $request->header('authorization'));
		$user = User::authUser(end($split_token));

		if(isset($user['success'])) {
			if($user['user']->school_id != null) {
				User::where('school_id', '=', $user['user']->school_id)
					->where('role', '!=', 'school_admin')
					->update(['active' => 1]);

				$url = ENV('SCHOOLS_URL_API') . 'schools/activate';
				$response = CurlHelper::curlPost($url, ['school_id' => $user['user']->school_id]);

				if(isset($response->success)) {
					return response()->json(['success' => 1])->setStatusCode(200, 'success');
				}

				return response()->json()->setStatusCode(449, 'error_school_not_found');
			}
		}
		return response()->json()->setStatusCode(463, 'error_user_not_found');
	}
}
