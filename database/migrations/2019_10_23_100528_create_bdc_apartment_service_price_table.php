<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcApartmentServicePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_apartment_service_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_service_id')->index('bdc_service_id');
            $table->integer('bdc_price_type_id')->index('bdc_price_type_id');
            $table->integer('bdc_apartment_id')->index('bdc_apartment_id');
            $table->string('name');
            $table->integer('price');
            $table->date('first_time_active');
            $table->date('last_time_pay');
            $table->integer('bdc_vehicle_id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_progressive_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_apartment_service_price');
    }
}
