<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentCode;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\CurlHelper;
use PDF;

class EnrollmentCodeController extends Controller
{    
    /**
     * @SWG\Post(path="/enrollmentCode/generate",
     *   summary="Generate new enrollment code.",
     *   description="Generate new enrollment code.",
     *   operationId="enrollmentCode",
     *   produces={"application/json"},
     *   tags={"Enrollment Codes"},
     *   @SWG\Response(response="200", description="['success' =>1, 'data'=> enrollment_code]")
     * )
    */
    public function generate(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $generated_code = mt_rand(100000,999999);

            $code = new EnrollmentCode();
            $code->code = $generated_code;

            if($user['user']->role == 'leader')
                $code->leader_id = $user['user']->user_id;
            if($user['user']->role == 'school_admin')
                $code->school_admin_id = $user['user']->user_id;

            $code->expiary_date = date("Y-m-d H:i:s", strtotime('+1 day'));

            if($code->save())
                return response()->json(['code'=>$generated_code])->setStatusCode(200, 'success');
            else
                return response()->json(['error' => 1])->setStatusCode(463, 'error_could_not_save');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');  
    }

    /**
     * @SWG\Get(path="/enrollmentCode/last",
     *   summary="Get latest generated enrollment code [for leaders only]",
     *   description="Get latest generated enrollment code [for leaders only]",
     *   operationId="getLatestCode",
     *   produces={"application/json"},
     *   tags={"Enrollment Codes"},
     *   @SWG\Response(response="200", description="['data' => data]")
     * )
    */
    public function lastGenerated(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $code = EnrollmentCode::where('leader_id', $user['user']->user_id)->orderBy('id', 'desc')->first();

            if($code) {
                $result['code'] = $code->code;
                if(time() < strtotime($code->expiary_date))
                    $result['expired'] = 0;
                else
                    $result['expired'] = 1;

                $result['expiary_date'] = $code->expiary_date;

                return response()->json(['data'=>$result])->setStatusCode(200, 'success');
            }
            return response()->json(['data'=>[]])->setStatusCode(200, 'success');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Post(path="/enrollmentCode/verify",
     *   summary="Verify that the enrollment code for the invited user is valid [available for leaders and students]",
     *   description="Verify that the enrollment code for the invited user is valid [available for leaders and students]",
     *   operationId="enrollmentCodeVerify",
     *   produces={"application/json"},
     *   tags={"Enrollment Codes"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="enrollment_code",
     *     description="Enrollment code",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Response(response="200", description="['success' => 1]")
     * )
    */
    public function verify(Request $request) {
        $validator = \Validator::make($request->all(), [
            'enrollment_code' => 'required'
        ]);          
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $check = EnrollmentCode::where('code', $request->get('enrollment_code'))->first();

        if($check && strtotime('now') < strtotime($check->expiary_date)) {
            $user = User::where('enrollment_code', $request->get('enrollment_code'))->first();
            if($user) {
                if($user->pending == 1)
                    return response()->json(['success' => 1, 'status' => 'invited', 'role' => $user->role])->setStatusCode(200, 'success');

                if($user->invited_by != null)
                    return response()->json()->setStatusCode(459, 'error_enrollment_code_already_used');
            }   
            
            return response()->json(['success' => 1, 'status' => 'signup'])->setStatusCode(200, 'success');
        }
        return response()->json()->setStatusCode(489, 'error_enrollment_code_expired');
    }

    public function generateSchool(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $generated_code = mt_rand(100000,999999);

            $code = EnrollmentCode::where('school_id', $user['user']->school_id)->first();
            if(!$code)
                $code = new EnrollmentCode();
            
            $code->code = $generated_code;
            $code->school_id = $user['user']->school_id;
            $code->school_admin_id = $user['user']->user_id;
            $code->expiary_date = date("Y-m-d H:i:s", strtotime('+3 year'));

            $input = $request->all();
            $input['name'] = 'school'.$user['user']->school_id.'.pdf';
            $input['name_thumbnail'] = 'school'.$user['user']->school_id.'.pdf';
            $input['school_id'] = $user['user']->school_id;
            $input['owner_id'] = $user['user']->user_id;
            $input['owner_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
            $input['type'] = 'poster';
            $input['group_id'] = '';
            $input['size'] = 0.00;// round($request->file('file')->size, 2);

            $data['code'] = str_split($generated_code);
            $pdf = PDF::loadView('enrollmentCodePdf', $data)->save(env('FILE_STORAGE').'/school'.$user['user']->school_id.'.pdf');
            $documents_url      = env('DOCUMENTS_URL_API').'documents/store';
            $response           = CurlHelper::curlPost($documents_url, $input);

            $schools_url = env('SCHOOLS_URL_API').'schools/enrollmentCode';
            $response_schools = CurlHelper::curlPost($schools_url, ['school_id' => $user['user']->school_id, 'code' => $generated_code, 'poster_id' => $response->document->id]);

            if(isset($response_schools->success)) {
                if($code->save())
                    return response()->json(['code'=>$generated_code, 'poster_id' => $response->document->id])->setStatusCode(200, 'success');
                else
                    return response()->json(['error' => 1])->setStatusCode(463, 'error_could_not_save');
            }
            return response()->json(['error' => 1])->setStatusCode(463, 'error_could_not_save');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function pdf()
    {
        $data = [];
        $pdf = PDF::loadView('enrollmentCodePdf', $data)->save(env('FILE_STORAGE').'/pdfff.pdf');
    }
}