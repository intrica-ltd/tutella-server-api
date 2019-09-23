<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFacebookFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_feed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->bigInteger('item_id');
            $table->text('permalink');
            $table->text('media_url');
            $table->dateTime('date_uploaded');
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
        Schema::dropIfExists('facebook_feed');
    }
}
