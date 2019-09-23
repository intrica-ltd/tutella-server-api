<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBankStatements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_statement', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('invoice_id');
            $table->text('name');
            $table->text('type');
            $table->text('path');
            $table->decimal('size', 8, 2);
            $table->integer('owner_id');
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
        Schema::dropIfExists('bank_statement');
    }
}
