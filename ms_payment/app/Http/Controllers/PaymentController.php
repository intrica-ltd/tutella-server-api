<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use App\Models\schoolPayment;
use App\Models\PayPalPayment;

class PaymentController extends Controller
{
    //
    public function setupPayment(Request $req)
    {
        if($req->has('school_payment_id')) {
            $school_payment_id = $req->get('school_payment_id');
            $schoolPayment = schoolPayment::where('id',$school_payment_id)->first();

            if(empty($schoolPayment))
                return ['error'=>1,'errors'=>['School payment not found']];
            else if( !empty($schoolPayment->payed))
                return ['error'=>1,'errors'=>['School payment already payed']];

            $total = $schoolPayment->total_value;
        } else {
            $total = schoolPayment::where('school_id', $req->get('school_id'))->where('payed', 0)->sum('total_value');

            if(!$total || $total <= 0)
                return ['error'=>1,'errors'=>['School payment already payed']];
        }
        
        $return_url = $req->get('return_url');
        $cancel_url = $req->get('cancel_url');
        $school_name= $req->get('school_name');

        $url = ENV("PAYMENT_API_URL")."/v1/oauth2/token";
        $authorization = base64_encode(ENV("PAYMENT_CLIENTID").":".ENV("PAYMENT_SECRET"));
        $response_at = Curl::to($url)            
            ->withHeaders(["Authorization: Basic $authorization","Accept: application/json","Content-Type: application/x-www-form-urlencoded"])
            ->withData(['grant_type'=>'client_credentials'])
            ->asJsonResponse()
            ->post();

        
        $access_token =" Bearer ".$response_at->access_token;
        $data = [
            
            'intent'        =>  'sale',
            'payer'         =>  ['payment_method'   =>  'paypal'],
            'transactions'  =>  [
                [
                    'amount' => ['total'  =>  $total ,'currency' => 'GBP'],                
                    "description" => "Montly payment for school $school_name"
                ]
            ],
            'redirect_urls' =>  ['return_url' => $return_url,'cancel_url' => $cancel_url]        
        ];
        
        $url = ENV("PAYMENT_API_URL")."/v1/payments/payment";
        $response = Curl::to($url)            
            ->withHeaders(["Authorization: $access_token","Content-Type: application/json"])
            ->withData(json_encode($data))
            ->asJsonResponse()
            ->post();

        return json_encode($response);
    }

    public function executePayment(Request $req)
    {

        if($req->has('school_payment_id')) {
            $school_payment_id = $req->get('school_payment_id');
            $schoolPayment = schoolPayment::where('id',$school_payment_id)->first();

            if(empty($schoolPayment))
                return ['error'=>1,'errors'=>['School payment not found']];
            else if( !empty($schoolPayment->payed))
                return ['error'=>1,'errors'=>['School payment already payed']];

            $total = $schoolPayment->total_value;
        } else {
            $total = schoolPayment::where('school_id', $req->get('school_id'))->where('payed', 0)->sum('total_value');

            if(!$total || $total <= 0)
                return ['error'=>1,'errors'=>['School payment already payed']];
        }

        $payment_id = $req->get('payment_id');        
        $payer_id   = $req->get('payer_id');

        // GET ACCESS TOKEN FOR PAYPAL
        $url = ENV("PAYMENT_API_URL")."/v1/oauth2/token";
        $authorization = base64_encode(ENV("PAYMENT_CLIENTID").":".ENV("PAYMENT_SECRET"));
        $response_at = Curl::to($url)            
            ->withHeaders(["Authorization: Basic $authorization","Accept: application/json","Content-Type: application/x-www-form-urlencoded"])
            ->withData(['grant_type'=>'client_credentials'])
            ->asJsonResponse()
            ->post();

        $access_token =" Bearer ".$response_at->access_token;
        
        // EXECUTE PAYMENT
        $data = [
            
            'payer_id'      =>  $payer_id,
            'transactions'  =>  [
                [
                    'amount' => ['total'  =>  $total ,'currency' => 'GBP'],                
                ]
            ],
        ];
        
        $url = ENV("PAYMENT_API_URL")."/v1/payments/payment/".$payment_id.'/execute';

        $response = Curl::to($url)            
            ->withHeaders(["Authorization: $access_token","Content-Type: application/json"])
            ->withData(json_encode($data))
            ->asJsonResponse()
            ->post();

        if(isset($response->intent)) {
            if($req->has('school_payment_id')) {
                $payment = schoolPayment::where('id', $req->get('school_payment_id'))->first();
                $payment->payed = 1;
                $payment->payment_type = 1;
                $payment->date_payed = date('Y-m-d H:i:s');
                $payment->save();

                $paypalPayment = new PayPalPayment();
                $paypalPayment->school_montly_payment_id = $req->get('school_payment_id');
                $paypalPayment->paypal_payment_id = $payment_id;
                $paypalPayment->total = $total;
                $paypalPayment->save();
            } else {
                $payment_ids = schoolPayment::where('school_id', $req->get('school_id'))->where('payed', 0)->pluck('id')->toArray();

                foreach($payment_ids as $id) {
                    $payment = schoolPayment::where('id', $id)->first();
                    $payment->payed = 1;
                    $payment->payment_type = 1;
                    $payment->date_payed = date('Y-m-d H:i:s');
                    $payment->save();
    
                    $paypalPayment = new PayPalPayment();
                    $paypalPayment->school_montly_payment_id = $id;
                    $paypalPayment->paypal_payment_id = $payment_id;
                    $paypalPayment->total = $total;
                    $paypalPayment->save();
                }
            }    

            return json_encode($response);
        }
    }
}
