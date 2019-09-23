<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CurlHelper;

class SchoolPackage extends Model
{
    protected $table = 'school_package';
    public $timestamps = false;

    public static function checkPackage($school_id)
    {        
        $users = User::where('school_id', $school_id)->whereIn('role', ['leader', 'student'])->where('active', 1)->get();
        $count_users = $users->count();

        $package = SchoolPackage::join('billing_packages', 'school_package.package_id', 'billing_packages.id')->where('school_id', $school_id)->first();

        if($count_users > $package->max_users && $package->package_id != 4) {
            $next_package = $package->package_id + 1;

            $payment_url = ENV('PAYMENT_API_URL').'billing/store/'.$school_id.'/'.$next_package;
            $response_payment = CurlHelper::curlPost($payment_url, []);

            SchoolPackage::where('school_id',$school_id)->update(["package_id" => $next_package]);
        }

        if($count_users < $package->min_users && $package->package_id != 1) {
            $next_package = $package->package_id - 1;

            $payment_url = ENV('PAYMENT_API_URL').'billing/store/'.$school_id.'/'.$next_package;
            $response_payment = CurlHelper::curlPost($payment_url, []);

            SchoolPackage::where('school_id',$school_id)->update(["package_id" => $next_package]);
        }
    }
}
