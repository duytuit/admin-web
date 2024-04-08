<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApartmentServicePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_service_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_service_id');
            $table->integer('bdc_price_type_id');
            $table->integer('bdc_apartment_id');
            $table->integer('bdc_vehicle_id');
            $table->integer('bdc_building_id');
            $table->string('name');
            $table->float('price');
            $table->tinyInteger('type')->default(0)->comment('0: không phải phương tiện, 1: phương tiện');
            $table->dateTime('first_time_active');
            $table->dateTime('last_time_pay');
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
        Schema::dropIfExists('apartment_service_price');
    }
}
