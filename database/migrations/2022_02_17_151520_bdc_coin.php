<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BdcCoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_coin', function (Blueprint $table)
        {
            $table->integer('bdc_building_id');
            $table->integer('bdc_apartment_id');
            $table->integer('bdc_apartment_service_price_id'); // 0 là số tiền chưa được chỉ định
            $table->integer('coin');
            // create index
            $table->unique(['bdc_apartment_id','bdc_apartment_service_price_id'],'index_partment_service');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_coin');
    }
}
