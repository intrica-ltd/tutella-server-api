<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchoolInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schoolInvoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('montly_payment_id')->nullable();  
            $table->unsignedInteger('billing_package_id')->nullable();  
            $table->integer('school_id');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->integer('discount')->default(0);
            $table->decimal('discount_value')->default(0);
            $table->decimal('value')->default(0);  
            $table->integer('calculated')->default(0);      
            $table->timestamps();
            $table->foreign('montly_payment_id')->references('id')->on('schoolPayments');
            $table->foreign('billing_package_id')->references('id')->on('billingPackages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schoolInvoices');
    }
}
