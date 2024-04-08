<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcServicePartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_service_partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pub_users_id')->nullable()->unsigned();
            $table->integer('bdc_business_partners_id')->nullable()->unsigned();
            $table->string('timeorder')->nullable()->comment('thời gian đặt');
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->integer('bdc_building_id');
            $table->integer('approved_id')->index('approved_id')->nullable()->unsigned();
            $table->string('confirm_date')->index('confirm_date')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('bdc_service_partners');
    }
}
