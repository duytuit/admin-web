<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcApartmentReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_apartment_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->integer('bdc_apartment_id')->index('bdc_apartment_id');
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
        Schema::dropIfExists('bdc_apartment_receipts');
    }
}
