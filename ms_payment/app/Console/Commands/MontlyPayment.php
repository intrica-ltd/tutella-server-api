<?php

namespace App\Console\Commands;

use App\Services\CurlClient;
use Illuminate\Console\Command;
use App\Models\schoolPackage;
use App\Models\schoolInvoices;
use App\Models\schoolPayment;

class MontlyPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:montly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate montly rate for school';

    /**
     * @var string
     */
    private $notificationsUrl;

    /**
     * @var CurlClient
     */
    private $curlService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CurlClient $curlService)
    {
        parent::__construct();

        $this->curlService = $curlService;

        $this->notificationsUrl = env('NOTIFICATIONS_URL');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Calculate montly rate for schools");
        $schools = schoolPackage::pluck('school_id');
        $date_last_month = new \Carbon\Carbon("first day of last month");
        $date = date('Y-m-d 00:00:00',strtotime($date_last_month->toDateTimeString()));

        foreach($schools as $school)
        {
            $sp = schoolPayment::where('school_id',$school)->where('month',$date_last_month->month)->count();
            if(empty($sp))
            {
                $this->info("(School_id : $school)  No school payment for this month... i will create one $date_last_month->month - $date_last_month->year");
                // if we calculate schoolPayment for last month for the first time
                
                $check_just_one_invoice = schoolInvoices::where('school_id',$school)->where( 'start_date', '>=', $date )->where('start_date', '<', date('Y-m-01 00:00:00'))->count();
                $schoolPackage = schoolPackage::where('school_id',$school)
                                        ->join('billingPackages','billingPackages.id','=','schoolPackage.billing_package_id')
                                        ->first();
                                        
                // if we calculate schoolPayment from most used way -once in a month with out changes of packages
                if($check_just_one_invoice == 1)
                {
                    $this->info("Just one invoice for this month");
                    $si = schoolInvoices::where('school_id',$school)->where( 'start_date', '>=', $date )->where('start_date', '<', date('Y-m-01 00:00:00'))->first();                                     
                    $si->discount          = 0;
                    $si->discount_value    = 0;
                    $si->value             = $schoolPackage->price; 
                    $si->calculated        = 1;
                    $si->end_date          = date('Y-m-d H:i:s');
                    $si->save();
                    $total_value = $schoolPackage->price;
                } else {
                    $si = schoolInvoices::where('school_id',$school)->where( 'start_date', '>=', $date )->where('start_date', '<', date('Y-m-01 00:00:00'))->get();
                    $total_value = 0;
                    $this->info("More than one invoice for this month");
                    foreach($si as $inv)
                    {
                        $total_value        =   $total_value + $inv->value;                        
                        $inv->calculated    = 1;
                        if(empty($inv->end_date))
                            $inv->end_date      = date('Y-m-d H:i:s');
                        $inv->save();
                    }
                }

                $invoice_number = $school.$date_last_month->month.$date_last_month->year;

                $schoolPayment = new schoolPayment();
                $schoolPayment->invoice_number = $invoice_number;
                $schoolPayment->school_id = $school;
                $schoolPayment->month = $date_last_month->month;
                $schoolPayment->year = $date_last_month->year;
                $schoolPayment->total_value = $total_value;
                $schoolPayment->save();

                $notification_data = ['school_id' => $school, 'month' => $date_last_month->month, 'year' => $date_last_month->year];
                $this->curlService->curlPost($this->notificationsUrl . 'billing/invoiceCreated', $notification_data);

                $this->info("Payment created");

            } else {
                $si = schoolInvoices::where( 'start_date', '>=', $date )->where('start_date', '<', date('Y-m-01 00:00:00'))->get();
                $total_value = 0;
                $this->info("More than one invoice for this month");
                foreach($si as $inv)
                {
                    $total_value        =   $total_value + $inv->value;                        
                    $inv->calculated    = 1;
                    if(empty($inv->end_date))
                        $inv->end_date      = date('Y-m-d H:i:s');
                    $inv->save();
                }

                $schoolPayment = schoolPayment::where('school_id',$school)
                                    ->where('month',$date_last_month->month)
                                    ->where('year',$date_last_month->year)
                                    ->first();
                
                $schoolPayment->total_value = $total_value;
                $schoolPayment->save();

                $notification_data = ['school_id' => $school, 'month' => $date_last_month->month, 'year' => $date_last_month->year];
                $this->curlService->curlPost($this->notificationsUrl . 'billing/invoiceCreated', $notification_data);

                $this->info("Payment updated");
            }
        }
    }
}
