<?php

namespace App\Http\Controllers;

use App\CompanyDetails;
use App\BankAccountDetails;
use PDF;

use Illuminate\Http\Request;

class CompanyDetailsController extends Controller
{
    public function store(Request $request)
    {
        $bankDetails = BankAccountDetails::where('id', 1)->first();
        $companyDetails = CompanyDetails::where('id', 1)->first();

        if($request->has('company_name'))
            $companyDetails->company_name = $request->get('company_name');

        if($request->has('company_address'))
            $companyDetails->company_address = $request->get('company_address');

        if($request->has('company_registration_number'))
            $companyDetails->company_registration_number = $request->get('company_registration_number');

        if($request->has('bank_name'))
            $bankDetails->bank_name = $request->get('bank_name');

        if($request->has('bank_address'))
            $bankDetails->bank_address = $request->get('bank_address');

        if($request->has('account_number'))
            $bankDetails->account_number = $request->get('account_number');

        if($request->has('sort_code'))
            $bankDetails->sort_code = $request->get('sort_code');

        if($request->has('iban'))
            $bankDetails->iban = $request->get('iban');

        if($request->has('account_name'))
            $bankDetails->account_name = $request->get('account_name');

        if($request->has('swift'))
            $bankDetails->swift = $request->get('swift');

        $companyDetails->save();
        $bankDetails->save();

    }

    public function details()
    {
        $bankDetails = BankAccountDetails::where('id', 1)->first();
        $companyDetails = CompanyDetails::where('id', 1)->first();

        return response()->json(['company_details' => $companyDetails, 'bank_details' => $bankDetails])->setStatusCode(200, 'success');
    }

    public function pdf()
    {
        $bankDetails = BankAccountDetails::where('id', 1)->first();
        $companyDetails = CompanyDetails::where('id', 1)->first();
        $data = ['bankDetails' => $bankDetails, 'companyDetails' => $companyDetails];
        $pdf = PDF::loadView('bankDetails', $data)->save(env('FILE_STORAGE').'/bankDetails.pdf');
        return response()->download(env('FILE_STORAGE').'/bankDetails.pdf')->setStatusCode(200, 'success');
    }
}
