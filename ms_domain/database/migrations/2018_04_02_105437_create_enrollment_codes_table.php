<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnrollmentCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollment_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('code');
            $table->integer('student_id')->nullable();
            $table->integer('leader_id')->nullable();
            $table->integer('school_admin_id')->nullable();
            $table->datetime('expiary_date');
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
        Schema::dropIfExists('enrollment_codes');
    }
}
