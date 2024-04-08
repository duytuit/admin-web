<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingSendMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_setting_send_mail', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bdc_building_id');
            $table->unsignedTinyInteger('type')->comment('1: hoa don, 2: su kien');
            $table->string('status')->comment('array key pass notification system');
            $table->unsignedInteger('mail_template_id');
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
        Schema::dropIfExists('setting_send_mail');
    }
}
