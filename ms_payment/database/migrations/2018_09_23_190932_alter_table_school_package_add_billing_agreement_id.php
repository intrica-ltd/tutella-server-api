<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSchoolPackageAddBillingAgreementId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schoolPackage', function (Blueprint $table) {
            $table->integer('billing_agreement_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schoolPackage', function (Blueprint $table) {
            $table->dropColumn('billing_agreement_id');
        });
    }
}
