<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcVehicleCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_vehicle_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_vehicle_id')->default(0)->comment("Id vehicle");
            $table->string('code',45)->default('code cards');
            $table->integer('status')->default(0)->comment("loại cư dân");
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
        Schema::dropIfExists('bdc_vehicle_cards');
    }
}
