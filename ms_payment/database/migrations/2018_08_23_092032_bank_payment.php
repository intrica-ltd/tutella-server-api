<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BankPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bankPayment', function (Blueprint $table) {
            
            $table->increments('id');
            $table->unsignedInteger('school_montly_payment_id')->nullable();  
            $table->text('name')->nullable();        
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
        Schema::dropIfExists('bankPayment');
    }
}
