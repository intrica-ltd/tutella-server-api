<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaypalPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypalPayment', function (Blueprint $table) {
            
            $table->increments('id');
            $table->unsignedInteger('school_montly_payment_id')->nullable();  
            $table->text('info')->nullable();        
            $table->timestamps();

            $table->foreign('school_montly_payment_id')->references('id')->on('schoolPayments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypalPayment');
    }
}
