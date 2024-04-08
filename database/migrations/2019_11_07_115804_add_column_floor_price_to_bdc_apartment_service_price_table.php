<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFloorPriceToBdcApartmentServicePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_apartment_service_price', function (Blueprint $table) {
            $table->integer('floor_price')->nullable()->comment('phí tiền sàn nhà');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_apartment_service_price', function (Blueprint $table) {
            $table->dropColumn('floor_price');
        });
    }
}
