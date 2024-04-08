<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBuldingPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_bulding_place', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255);
            $table->string('description',255);
            $table->string('address',255);
            $table->string('mobile',255);
            $table->string('email',255);
            $table->integer('status');
            $table->integer('bdc_building_id');
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
        Schema::dropIfExists('bdc_bulding_place');
    }
}
