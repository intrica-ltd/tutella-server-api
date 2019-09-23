<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchoolBillingPackage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schoolPackage', function (Blueprint $table) {
            $table->integer('school_id');
            $table->unsignedInteger('billing_package_id')->nullable();       
            $table->timestamps();
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
        //
        Schema::dropIfExists('schoolPackage');
    }
}
