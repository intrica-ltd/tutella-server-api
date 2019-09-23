<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CurlHelper;
use App\Models\User;
use App\Models\SchoolPackage;
use PDF;
use DB;

class PaymentController extends Controller
{

    //
    public function setupPayment(Request $req)
    {
        $split_token = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $input = $req->all();
            $input['school_id'] = $user['user']->school_id;
            $url = ENV("PAYMENT_API_URL")."payment/setup";
            $response = CurlHelper::curlPost($url, $input);

            return json_encode($response);
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function executePayment(Request $req)
    {
        $split_token = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $input = $req->all();
            $input['school_id'] = $user['user']->school_id;
            $url = ENV("PAYMENT_API_URL")."payment/execute";
            $response = CurlHelper::curlPost($url, $input);

            return json_encode($response);
        }
    }

    public function markPaid(Request $request, $id)
    {
        $split_token = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $url = ENV("PAYMENT_API_URL")."markPaid/".$id;
            $response = CurlHelper::curlPost($url);

            if(isset($response->success))
                return response()->json()->setStatusCode(200, 'success');

            return response()->json($response)->setStatusCode(459, 'error');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function reject(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'reason' => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $url = ENV("PAYMENT_API_URL")."reject/".$id;
            $response = CurlHelper::curlPost($url, ['reason' => $request->get('reason')]);

            if(isset($response->success)) {
                $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $response->school_id;
                $response_school = CurlHelper::curlGet($url_school);
                
                $email_data['email'] = $response_school->data->school->email;
                $email_data['reason'] = $request->get('reason');
                \Mail::send('emails.paymentRejected', $email_data, function ($m) use ($email_data) {
                    $m->from('no-reply@tutella.com', '');
                    $m->to($email_data['email'], '')->subject('Payment rejected!');
                });

                return response()->json()->setStatusCode(200, 'success');
            }

            return response()->json($response)->setStatusCode(459, 'error');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function invoice(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {

            $input['school_id'] = ($user['user']->role == 'super_admin') ? 'all' : $user['user']->school_id;
            $url = ENV("PAYMENT_API_URL")."invoice/".$id;
            $response = CurlHelper::curlGet($url, $input);

            if(isset($response->success)) {
                $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $response->data->payment->school_id;
                $response_school = CurlHelper::curlGet($url_school);
                
                $school_info = [];
                if(isset($response_school->success)) {
                    $school_info = [
                        'school_name'   => $response_school->data->school->school_name,
                        'address'       => $response_school->data->school->address,
                        'email'         => $response_school->data->school->email,
                        'school_name'   => $response_school->data->school->school_name,
                    ];
                }
                $vat = number_format($response->data->payment_total * 0.2, 2);
                $subtotal = $response->data->payment_total - $vat;
                return response()->json(['payment' => $response->data->payment, 'invoices' => $response->data->invoices, 'documents' => $response->data->documents, 'school_data' => $school_info, 'payment_total' => $response->data->payment_total, 'subtotal' => $subtotal, 'vat' => $vat])->setStatusCode(200, 'success');
            }

            return response()->json($response)->setStatusCode(459, 'error');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function info(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $result = [];

            $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $user['user']->school_id;
            $response_school = CurlHelper::curlGet($url_school);
            if(isset($response_school->success)) {
                $result['school_info']['school_name'] = $response_school->data->school->school_name;
                $result['school_info']['status'] = $response_school->data->school->active;
                if($response_school->data->school->active == -1) {
                    $result['school_info']['expiration_date'] = date('Y-m-d H:i:s', strtotime("+15 day", strtotime($response_school->data->school->cancelled_at)));
                }
            }

            $result['school_info']['nusers'] = User::where('school_id', $user['user']->school_id)->where('active', 1)->whereIn('role', ['leader', 'student'])->count();

            $package = SchoolPackage::join('billing_packages', 'school_package.package_id', 'billing_packages.id')->where('school_id', $user['user']->school_id)->first();
            $result['school_info']['package'] = $package->name;
            $result['school_info']['max_users'] = $package->max_users;
            $result['school_info']['min_users'] = $package->min_users;

            $payments_url = env('PAYMENT_API_URL').'school/info/'.$user['user']->school_id;
            $response_payments = CurlHelper::curlGet($payments_url);
            
            if(isset($response_payments->success)) {
                $result['billing'] = $response_payments->data->billing;
                $result['billing_history'] = $response_payments->data->billing_history;
                return response()->json($result)->setStatusCode(200, 'success');
            }

            return response()->json($response_payments)->setStatusCode(480, 'error_fetching_data');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function upload(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'count'         => 'required'
        ]);
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            if(!$request->has('invoice_id')) {
                $payments_url      = env('PAYMENT_API_URL').'getUnpaidInvoices/'.$user['user']->school_id;
                $response_invoices          = CurlHelper::curlGet($payments_url);

                if(isset($response_invoices->success) && count($response_invoices->invoices) > 0) {
                    $input = [];
                    for($i=0; $i < $request->get('count'); $i++) {
                        $file = $request->file('file'.$i);

                        $file_name = md5($file->getClientOriginalName() . time());
                        $full_file_name = $file_name.'.'.$file->getClientOriginalExtension();
                        
                        foreach($response_invoices->invoices as $invoice) {
                            $input[] = [
                                'name' => $full_file_name,
                                'school_id' => $user['user']->school_id,
                                'owner_id' => $user['user']->user_id,
                                'type' => 'bank_statement',
                                'path' => '/payments/'.$full_file_name,
                                'size' => 0,
                                'invoice_id' => $invoice
                            ];
                        }
                        
                        $file->move(env('FILE_STORAGE').'/payments', $full_file_name);
                    }
                    $payments_url      = env('PAYMENT_API_URL').'upload';
                    $response          = CurlHelper::curlPost($payments_url, $input);

                    if(isset($response->success)) {
                        return response()->json()->setStatusCode(200, 'success');
                    }
                }
                return response()->json()->setStatusCode(459, 'error_no_invoices_found');

            } else {
                $input = [];
                for($i=0; $i < $request->get('count'); $i++) {
                    $file = $request->file('file'.$i);

                    $file_name = md5($file->getClientOriginalName() . time());
                    $full_file_name = $file_name.'.'.$file->getClientOriginalExtension();
                    
                    $input[] = [
                        'name' => $full_file_name,
                        'school_id' => $user['user']->school_id,
                        'owner_id' => $user['user']->user_id,
                        'type' => 'bank_statement',
                        'path' => '/payments/'.$full_file_name,
                        'size' => 0,
                        'invoice_id' => $request->get('invoice_id')
                    ];
                    $file->move(env('FILE_STORAGE').'/payments', $full_file_name);
                }
                $payments_url      = env('PAYMENT_API_URL').'upload';
                $response          = CurlHelper::curlPost($payments_url, $input);

                if(isset($response->success)) {
                    return response()->json()->setStatusCode(200, 'success');
                }
            }            
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function download(Request $request, $id) {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $payments_url      = env('PAYMENT_API_URL').'download/'.$id;
            $response          = CurlHelper::curlGet($payments_url);

            if(isset($response->success)) {
                return response()->download(env('FILE_STORAGE').'/payments/'.$response->document->name)->setStatusCode(200, 'success');
            }
            return response()->json()->setStatusCode(495, 'error_document_does_not_exist');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function invoicePdf(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {

            $input['school_id'] = ($user['user']->role == 'super_admin') ? 'all' : $user['user']->school_id;
            $url = ENV("PAYMENT_API_URL")."invoice/".$id;
            $response = CurlHelper::curlGet($url, $input);

            if(isset($response->success)) {
                $url_school = ENV('SCHOOLS_URL_API') . 'schools/' . $response->data->payment->school_id;
                $response_school = CurlHelper::curlGet($url_school);
                
                $school_info = [];
                if(isset($response_school->success)) {
                    $school_info = [
                        'school_name'   => $response_school->data->school->school_name,
                        'address'       => $response_school->data->school->address,
                        'email'         => $response_school->data->school->email,
                        'school_name'   => $response_school->data->school->school_name,
                    ];
                }
                $vat = number_format($response->data->payment_total * 0.2, 2);
                $subtotal = $response->data->payment_total - $vat;
                $data = ['payment' => $response->data->payment, 'invoices' => $response->data->invoices, 'school_data' => $school_info, 'payed_total' => $response->data->payment_total, 'vat' => $vat, 'subtotal' => $subtotal];
            
                $pdf = PDF::loadView('invoice', $data)->save(env('FILE_STORAGE').'/invoice.pdf');
                return response()->download(env('FILE_STORAGE').'/invoice.pdf')->setStatusCode(200, 'success');
            }

            return response()->json($response)->setStatusCode(459, 'error');
        }

        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }
    
    public function billingAgreement(Request $req)
    {
        $split_token  = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));
        
        if(isset($user['success'])) {
            $input = $req->all();
            $input['school_id'] = $user['user']->school_id;
            $url = ENV("PAYMENT_API_URL")."billingAgreement";
            $response = CurlHelper::curlPost($url, $input);

            if(isset($response->success))
                return response()->json(['approve_url' => $response->url])->setStatusCode(200, 'success');

            return response()->json($response)->setStatusCode(459, 'error');
        }
        
    }

    public function billingAgreementCallback(Request $request)
    {
        $input = ['token' => $request->get('token')];
        $url = ENV("PAYMENT_API_URL")."saveBillingAgreement";
        $response = CurlHelper::curlPost($url, $input);

        return redirect(env('APP_URL').'/billing?subscribed=success');
    }

    public function billingAgreementCancel(Request $request)
    {
        return redirect(env('APP_URL').'/billing');
    }
}
