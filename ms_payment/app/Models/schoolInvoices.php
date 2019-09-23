<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class schoolInvoices extends Model
{
    protected $table = 'schoolInvoices';
    public static function calculateSchoolInvoice($invoice,$school_id,$billingPackage)
    {
        $earlier = new \DateTime($invoice->start_date);
        $later   = new \DateTime();
        $days    = $later->diff($earlier)->format("%a");
        $d = new DateTime( $invoice->start_date ); 
        $days_last_month = $d->format( 't' );

        if($days > 0)
        {
            // get price for current billing plan
            $price_for_day = $billingPackage->price / $days_last_month;
            // get days from start
            $start_date = schoolInvoices::where('school_id',$school_id)->first();
            $start_date = $start_date->start_date;
            $end_discount_date = strtotime($start_date. ' + 2 days');
            $invoice->qty = $days / $days_last_month;
            // end discount date is greater than today school is in discount period
            if($end_discount_date >= time())
            {
                $invoice->discount = 50;
                $invoice->discount_value = $days * ($price_for_day / 2);
                $invoice->value = $days * ($price_for_day / 2);
                $invoice->end_date = date('Y-m-d H:i:s');
                $invoice->save();

            } else {
                $on_discount        =   new \DateTime(date('Y-m-d',$end_discount_date));
                $later              =   new \DateTime();
                $days_not_discount  =   $later->diff($on_discount)->format("%a");
                
                $days_on_discount   =   $on_discount->diff($earlier)->format("%a");

                $price_not_discount = $price_for_day * $days_not_discount;
                $price_discount     = ($price_for_day / 2 ) * $days_on_discount; 

                $invoice->discount = 50;
                $invoice->discount_value = $price_discount;
                $invoice->value = $price_not_discount + $price_discount;
                $invoice->end_date = date('Y-m-d H:i:s');
                $invoice->save();

            }
            
        }
        elseif($days == 0)
        {
            $invoice->qty = 0;
            $invoice->end_date = date('Y-m-d H:i:s');
            $invoice->discount = 0;
            $invoice->discount_value = $billingPackage->price / $days_last_month;
            $invoice->value = $billingPackage->price / $days_last_month;
            $invoice->save();
        }  
    }
}