<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\CurlHelper;

class DashboardController extends Controller
{
    /**
     * @SWG\Get(path="/dashboard/info",
     *   summary="Dashboard info.",
     *   description="Dashboard info.",
     *   operationId="dashboardInfo",
     *   produces={"application/json"},
     *   tags={"Dashboard"},
     *   @SWG\Response(response="200", description="[user_data]")
     * )
    */
    public function info(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $active_users = User::where('school_id', $user['user']->school_id)->where('active', 1)->whereIn('role', ['student', 'leader'])->count();

            $documents_url     = env('DOCUMENTS_URL_API').'documents/'.$user['user']->school_id.'/totalDocuments';
            $response_doc   = CurlHelper::curlGet($documents_url);
            
            $total_documents = 0;
            if(isset($response_doc->success))
                $total_documents = $response_doc->count_documents;
                
            $groups_url = env('GROUPS_URL_API').'groups/'.$user['user']->school_id.'/total';
            $response_groups = CurlHelper::curlGet($groups_url);

            $total_groups = 0;
            if(isset($response_groups->success))
                $total_groups = $response_groups->count_groups;

            $payments_url = env('PAYMENT_API_URL').$user['user']->school_id.'/overdue';
            $response_payments = CurlHelper::curlGet($payments_url);

            $total_overdue = 0;
            if(isset($response_payments->success))
                $total_overdue = $response_payments->total_overdue;

            $input['school_id'] = $user['user']->school_id;
            $surveys_url    = env('SURVEYS_URL_API').'list';
            $response_surveys       = CurlHelper::curlGet($surveys_url, $input);

            $surveys = [];
            if(isset($response_surveys->success)) {
                $surveys['total_surveys'] = $response_surveys->data->total;
                $surveys['total_assignees'] = $response_surveys->data->total_assignees;
                $surveys['surveys_done'] = $response_surveys->data->surveys_done;
                $surveys['surveys_canceled'] = $response_surveys->data->surveys_canceled;
                $surveys['surveys_expired'] = $response_surveys->data->surveys_expired;
            }

            $last_survey = [];
            $surveys_url = env('SURVEYS_URL_API').$user['user']->school_id.'/lastSurvey';
            $response_surveys = CurlHelper::curlGet($surveys_url);
            if(isset($response_surveys->success)) {
                $last_survey = $response_surveys->data;
            }

            $result = [
                'success' => 1, 
                'details' => [
                    'users' => $active_users, 
                    'photos' => $total_documents, 
                    'groups' => $total_groups, 
                    'total_overdue' => $total_overdue, 
                    'surveys' => $surveys,
                    'last_survey' => $last_survey
                ]
            ];

            return response()->json($result)->setStatusCode(200, 'success');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function contactUs(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from_email'       => 'required|email',
            'message'          => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $email_data = [];
        $email_data['subject'] = ($request->has('subject')) ? $request->get('subject') : '';
        $email_data['body'] = $request->get('message');
        $email_data['from_email'] = $request->get('from_email');

        \Mail::send('emails.contact-us', $email_data, function ($m) use ($email_data) {
            $m->from($email_data['from_email'], '');
            $m->to('no-reply@tutella.com', '')->subject($email_data['subject']);
        });

        return response()->json()->setStatusCode(200, 'success');
    }
}