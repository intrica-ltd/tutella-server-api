<?php

namespace App\Http\Controllers;

use App\Models\schoolPackage;
use App\Models\schoolPayment;
use App\Models\BankPayment;
use App\Models\schoolInvoices;
use App\Models\BankStatements;
use App\Models\BillingPackages;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function markPaid($id)
    {
        $schoolPayment = schoolPayment::where('id', $id)->first();
        $schoolPayment->payed = 1;
        $schoolPayment->payment_type = 2;
        $schoolPayment->date_payed = date('Y-m-d H:i:s');
        $schoolPayment->save();

        $bankPayment = new BankPayment();
        $bankPayment->school_montly_payment_id = $id;
        $bankPayment->save();

        return ['success' => 1];
    }

    public function reject(Request $request, $id)
    {
        $schoolPayment = schoolPayment::where('id', $id)->first();
        $schoolPayment->payed = 0;
        $schoolPayment->payment_type = null;
        $schoolPayment->date_payed = null;
        $schoolPayment->save();

        $update = BankStatements::where('invoice_id', $id)
                    ->update(['rejected' => 1, 'reason' => $request->get('reason')]);

        return ['success' => 1, 'school_id' => $schoolPayment->school_id];
    }

    public function schoolInfo($school_id)
    {
        $overdue = schoolPayment::where('school_id', $school_id)->where('payed', '!=', 1)->get()->sum('total_value');
        $billingHistory = schoolPayment::where('school_id', $school_id)->select('id as invoice_id', 'invoice_number', 'month', 'payed as invoice_status', 'date_payed as pay_date', 'payment_type as payment_method', 'total_value as amount', 'created_at as invoice_date')->orderBy('invoice_id', 'desc')->get();

        return ['success' => 1, 'data' => ['overdue' => number_format($overdue, 2), 'billing_history' => $billingHistory]];
    }

    public function schoolsInfo()
    {
        $revenue = schoolPackage::with('package')->get()->sum('package.price');
        $overdue = schoolPayment::where('payed', '!=', 1)->get()->sum('total_value');
        return ['success' => 1, 'data' => ['revenue' => number_format($revenue, 2), 'overdue' => number_format($overdue, 2)]];
    }

    public function invoice(Request $request, $id)
    {
        $payment = schoolPayment::where('id', $id)->select('id', 'school_id', 'invoice_number', 'created_at', 'year', 'month', 'payed', 'payment_type')->first();
        if(!$payment)
            return ['error' => 1];

        if($request->get('school_id') != 'all' && $payment->school_id != $request->get('school_id'))
            return ['error' => 1, 'message' => 'not_allowed'];

        $start_date = date('Y-m-01 00:00:00', strtotime($payment->year.'-'.$payment->month.'-01'));
        $end_date = date('Y-m-t 23:59:59', strtotime($payment->year.'-'.$payment->month.'-01'));

        if($request->get('school_id') != 'all')
            $invoices = schoolInvoices::join('billingPackages', 'billingPackages.id', 'schoolInvoices.billing_package_id')
                            ->where('schoolInvoices.start_date', '>=', $start_date)->where('schoolInvoices.end_date', '<=', $end_date)
                            ->where('schoolInvoices.school_id', $request->get('school_id'))
                            ->select('schoolInvoices.*', 'billingPackages.name', 'billingPackages.min_users', 'billingPackages.max_users', 'billingPackages.price')
                            ->get(); 
        else
            $invoices = schoolInvoices::join('billingPackages', 'billingPackages.id', 'schoolInvoices.billing_package_id')
                            ->join('schoolPayments', 'schoolPayments.school_id', 'schoolInvoices.school_id')
                            ->where('schoolPayments.id', $id)
                            ->where('schoolInvoices.start_date', '>=', $start_date)->where('schoolInvoices.end_date', '<=', $end_date)
                            ->select('schoolInvoices.*', 'billingPackages.name', 'billingPackages.min_users', 'billingPackages.max_users', 'billingPackages.price')
                            ->get(); 

        $response = ['payment' => $payment, 'invoices' => $invoices];
        $response['documents'] = [];

        $documents = BankStatements::where('invoice_id', $payment->id)->select('id', 'name')->get();
        if(count($documents) > 0)
            $response['documents'] = $documents;

        $response['payment_total'] = $invoices->sum('value');

        return ['success' => 1, 'data' => $response];
    }

    public function schoolInfoId($school_id)
    {
        $package = schoolPackage::join('billingPackages', 'billingPackages.id', 'schoolPackage.billing_package_id')
                        ->where('school_id', $school_id)
                        ->first();

        $overdue = schoolPayment::where('school_id', $school_id)->where('payed', '!=', 1)->get()->sum('total_value');

        $invoices = SchoolInvoices::where('school_id', $school_id)->where('start_date', '>=', date('Y-m-01 00:00:00'))->get();
        if(count($invoices) > 0) {
            $next_invoice = 0; $i = 0;
            foreach($invoices as $invoice) {
                if($invoice->calculated == 1) {
                    $next_invoice += $invoice->value;
                } else {
                    if($invoice->billing_package_id > $invoices[$i]->billing_package_id)
                        $start_date = $invoice->start_date;
                    else
                        $start_date = $invoices[$i]->start_date;

                    $package_price = BillingPackages::where('id', $invoice->billing_package_id)->first();
                    $remaining_days = date_diff(new \DateTime(date('Y-m-t 00:00:00')), new \DateTime(date('Y-m-d 00:00:00', strtotime($start_date))));
                    $days = $remaining_days->days;
                    $days_this_month = date('t');
                    $price_per_day = $package_price->price / $days_this_month;
                    $next_invoice += $price_per_day*$days;
                }
                $i++;
            }
        } else {
            if($package)                        
                $next_invoice = $package->price;
            else
                $next_invoice = 0.00;
                        
        }
        
        $billingHistory = schoolPayment::where('school_id', $school_id)
                                ->select('id as invoice_id', 'invoice_number', 'month', 'payed as invoice_status', 'date_payed as pay_date', 'payment_type as payment_method', 'total_value as amount', 'created_at as invoice_date')
                                ->orderBy('id', 'desc')
                                ->get();
        
        return ['success' => 1, 'data' => ['billing' => ['overdue' => number_format($overdue, 2), 'total_due' => number_format($overdue, 2), 'next_invoice' => $next_invoice, 'subscribed' => $package->state], 'billing_history' => ['total_payments' => $billingHistory->count(), 'invoices' => $billingHistory]]];
    }

    public function upload(Request $request)
    {
        $inputs = $request->all();
        foreach($inputs as $input) {
            BankStatements::create($input);
        }

        $schoolPayment = schoolPayment::where('id', $inputs[0]['invoice_id'])->first();
        $schoolPayment->payed = -1;
        $schoolPayment->save();
        
        return ['success' => 1];
    }

    public function download($id)
    {
        $doc = BankStatements::where('id', $id)->first();
        if(!$doc)
            return ['error' => 1];

        return ['success' => 1, 'document' => ['name' => $doc->name]];
    }

    public function overdue($school_id)
    {
        $overdue = schoolPayment::where('school_id', $school_id)->where('payed', '!=', 1)->sum('total_value');

        return ['success' => 1, 'total_overdue' => number_format($overdue, 2)];
    }

    public function getUnpaidInvoices($school_id)
    {
        $invoices = schoolPayment::where('school_id', $school_id)->where('payed', '!=', 1)->pluck('id')->toArray();
        return ['success' => 1, 'invoices' => $invoices];
    }

    public function billingAgreement(Request $request)
    {
        $schoolPackage = schoolPackage::join('billingPackages', 'billingPackages.id', 'schoolPackage.billing_package_id')
                            ->where('schoolPackage.school_id', $request->get('school_id'))
                            ->first();

        if(!$schoolPackage)
            return ['error' => 1];

        if($schoolPackage->state == 1)
            return ['error' => 1, 'error_message' => 'Already subscribed'];


        $url = ENV("PAYMENT_API_URL")."/v1/oauth2/token";
        $authorization = base64_encode(ENV("PAYMENT_CLIENTID").":".ENV("PAYMENT_SECRET"));
        $response_at = \Curl::to($url)            
            ->withHeaders(["Authorization: Basic $authorization","Accept: application/json","Content-Type: application/x-www-form-urlencoded"])
            ->withData(['grant_type'=>'client_credentials'])
            ->asJsonResponse()
            ->post();

        $access_token =" Bearer ".$response_at->access_token;
        $data = [
            'name'                  =>  'Agreement for subscibtion',
            'description'           =>  'Desc',
            'start_date'            =>  '2018-10-01T14:36:21Z',
            'payer'                 =>  [
                'payment_method'  =>  'paypal'
            ],
            'plan'                  =>  [
                'id'            =>  $schoolPackage->paypalID
            ]
        ];
        
        $url = ENV("PAYMENT_API_URL")."/v1/payments/billing-agreements";
        $response = \Curl::to($url)            
            ->withHeaders(["Authorization: $access_token","Content-Type: application/json"])
            ->withData(json_encode($data))
            ->asJsonResponse()
            ->post();   

        if(isset($response->links[0]->href)) {
            $token = explode('token=', $response->links[0]->href);

            schoolPackage::where('school_id', $request->get('school_id'))->update(['pp_return_url' => $token[1]]);
            return ['success' => 1, 'url' => $response->links[0]->href];
        }
        return ['error' => 1];
    }

    public function saveBillingAgreement(Request $request)
    {
        $schoolPackage = schoolPackage::where('pp_return_url', $request->get('token'))->first();
        
        if(!$schoolPackage)
            return ['error' => 1];

        schoolPackage::where('pp_return_url', $request->get('token'))->update(['state' => 1]);
        return ['success' => 1];
    }

    public function totalSchoolOverdue($school_id)
    {
        $overdue = schoolPayment::where('school_id', $school_id)->where('payed', '!=', 1)->sum('total_value');

        $invoices = SchoolInvoices::where('school_id', $school_id)->where('start_date', '>=', date('Y-m-01 00:00:00'))->get();
        if(count($invoices) > 0) {
            $next_invoice = 0; $i = 0;
            foreach($invoices as $invoice) {
                if($invoice->calculated == 1) {
                    $overdue += $invoice->value;
                } else {
                    if($invoice->billing_package_id > $invoices[$i]->billing_package_id)
                        $start_date = $invoice->start_date;
                    else
                        $start_date = $invoices[$i]->start_date;

                    $package_price = BillingPackages::where('id', $invoice->billing_package_id)->first();
                    $remaining_days = date_diff(new \DateTime(date('Y-m-t 00:00:00')), new \DateTime(date('Y-m-d 00:00:00', strtotime($start_date))));
                    $days = $remaining_days->days;
                    $days_this_month = date('t');
                    $price_per_day = $package_price->price / $days_this_month;
                    $overdue += $price_per_day*$days;
                }
                $i++;
            }
        } else {
            $package = schoolPackage::join('billingPackages', 'billingPackages.id', 'schoolPackage.billing_package_id')
                        ->where('school_id', $school_id)
                        ->first();

            if($package) {
                $package_price = $package->price;

                $remaining_days = date_diff(new \DateTime(date('Y-m-t 00:00:00')), new \DateTime(date('Y-m-d 00:00:00')));
                $days = $remaining_days->days;
                $days_this_month = date('t');
                $price_per_day = $package_price / $days_this_month;
                $overdue += $price_per_day*$days;

            }                         
        }

        return ['success' => 1, 'total_overdue' => number_format($overdue, 2)];
    }

    public function suspendAgreement(Request $request)
    {
        // $schoolPackage = schoolPackage::join('billingPackages', 'billingPackages.id', 'schoolPackage.billing_package_id')
        //                     // ->where('schoolPackage.school_id', $request->get('school_id'))
        //                     ->where('schoolPackage.school_id', 20)
        //                     ->first();

        // if($schoolPackage && $schoolPackage->pp_return_url != null) {
        //     $url = ENV("PAYMENT_API_URL")."/v1/oauth2/token";
        //     $authorization = base64_encode(ENV("PAYMENT_CLIENTID").":".ENV("PAYMENT_SECRET"));
        //     $response_at = \Curl::to($url)            
        //         ->withHeaders(["Authorization: Basic $authorization","Accept: application/json","Content-Type: application/x-www-form-urlencoded"])
        //         ->withData(['grant_type'=>'client_credentials'])
        //         ->asJsonResponse()
        //         ->post();

        //     $access_token =" Bearer ".$response_at->access_token;
        //     $data = [
        //         'note' => 'School is cancelled.'
        //     ];
            
        //     $url = ENV("PAYMENT_API_URL")."/v1/payments/billing-agreements/".$schoolPackage->pp_return_url.'/cancel';
        //     $response = \Curl::to($url)            
        //         ->withHeaders(["Authorization: $access_token","Content-Type: application/json"])
        //         ->withData(json_encode($data))
        //         ->asJsonResponse()
        //         ->post();  
                
        //     return $response;
        // }
    }
}
