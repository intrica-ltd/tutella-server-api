<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');

            $table->smallInteger('active')->default(0);
            $table->dateTime('activation_email_date')->nullable();
            $table->text('activation_hash')->nullable();
            $table->text('activation_code')->nullable();

            $table->text('new_email')->nullable();
            $table->text('reset_password_hash')->nullable();
            $table->dateTime('reset_password_asked')->nullable();
            $table->dateTime('reset_password_valid')->nullable();            
            $table->text('reset_email_hash')->nullable();
            $table->dateTime('reset_email_valid')->nullable();   

            $table->timestamps();
            $table->softDeletes();
            $table->text('deleted_by',150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
