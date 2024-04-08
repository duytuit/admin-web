<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountSmsAppEmailInTableBdcCampainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_campain', function (Blueprint $table) {
            $table->integer('sended_sms');
            $table->integer('sended_app');
            $table->integer('sended_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_campain', function (Blueprint $table) {
            //
        });
    }
}
