<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBankStatementAddRejectedReasonColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_statement', function (Blueprint $table) {
            $table->integer('rejected')->default(0)->after('owner_id');
            $table->text('reason')->after('rejected')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_statement', function (Blueprint $table) {
            $table->dropColumn('rejected');
            $table->dropColumn('reason');
        });
    }
}
