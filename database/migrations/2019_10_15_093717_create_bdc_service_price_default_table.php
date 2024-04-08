<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcServicePriceDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_service_price_default', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_service_id');
            $table->integer('bdc_price_type');
            $table->string('name');
            $table->float('price');
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
        Schema::dropIfExists('bdc_service_price_default');
    }
}
