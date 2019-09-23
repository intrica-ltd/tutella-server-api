<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\schoolPackage;
use App\Models\schoolInvoices;

class BillingController extends Controller
{
    //

    public function listSchoolPackage($school_id)
    {
        return schoolPackage::where('school_id',$school_id)->get()->toArray();
    }

    public function listAllPackages()
    {
       return  \DB::table("billingPackages")->select('id','name','max_users','price')->get()->toArray();
    }

    public function store($school_id,$package_id)
    {
        // Check schoolPackage
        $schoolPack  = schoolPackage::where('school_id',$school_id)->first();
        // First check active user of school
        $users_count    = \DB::table("tutella-domain.users")->where('school_id',$school_id)->where('active',1)->count();
        $billingPackage = \DB::table("billingPackages")->select('max_users','price')->where('id',$package_id)->first();
        if(!$billingPackage)
            return ['error'=>1,'errors' => ['Billing package does not exsist']];
        //if active users are more than package max user return errror
        if($billingPackage->max_users < $users_count)
            return ['error'=>1,'errors' => ['You have more active users than maximum for this package']];

        if(empty($schoolPack))
        {
            if($billingPackage)
            //insert billing package for school
            $schoolPack = new schoolPackage();
            $schoolPack->school_id = $school_id;
            $schoolPack->billing_package_id = $package_id;
            $schoolPack->save();
            
            // init first invoice in schoolInvoices table
            $data = 
            [
                "school_id"     => $school_id,
                "billing_package_id" => $package_id,
                "start_date"    => date('Y-m-d H:i:s')                
            ];
            schoolInvoices::insert($data);
        }
        else
        {
            if($schoolPack->billing_package_id == $package_id)
                return ['error'=>1,'errors' => ['Your schoool is already assigned to this billing plan']];
            // else calculate price with current package and add new with new package
            $invoice = schoolInvoices::where('school_id',$school_id)->where('end_date',null)->first();
            if($invoice)
            {
                schoolInvoices::calculateSchoolInvoice($invoice,$school_id,$billingPackage);
            }
            $data = 
            [
                "school_id"     => $school_id,
                "billing_package_id" => $package_id,
                "start_date"    => date('Y-m-d H:i:s')                
            ];
            schoolInvoices::insert($data);
            // update billing package for school
            schoolPackage::where('school_id',$school_id)->update(["billing_package_id" => $package_id]);
            
        }

        return ['success' => 1];
    }
}
