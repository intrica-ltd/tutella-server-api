<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePaypalPaymentAddPaymentIdTotalColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paypalPayment', function (Blueprint $table) {
            $table->integer('paypal_payment_id')->after('info');
            $table->integer('total')->after('paypal_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paypalPayment', function (Blueprint $table) {
            $table->dropColumn('paypal_payment_id');
            $table->dropColumn('total');
        });
    }
}
