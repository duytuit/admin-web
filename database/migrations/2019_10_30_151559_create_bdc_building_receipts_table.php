<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBuildingReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_building_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->string('name');
            $table->integer('old_total');
            $table->integer('new_total');
            $table->integer('cost');
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
        Schema::dropIfExists('bdc_building_receipts');
    }
}
