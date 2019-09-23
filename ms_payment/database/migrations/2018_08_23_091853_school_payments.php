<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchoolPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schoolPayments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('month');
            $table->integer('year');
            $table->integer('payed')->default(0);
            $table->decimal('total_value')->default(0); 
            $table->text('payment_type',100)->nullable();
            $table->datetime('date_payed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schoolPayments');
    }
}
