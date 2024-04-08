<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoVehicleCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_vehicles_category', function (Blueprint $table) {
            $table->boolean('status')->nullable();
            $table->date('first_time_active')->nullable();
            $table->tinyInteger('bdc_price_type_id')->nullable();
        });

        Schema::table('bdc_vehicles', function (Blueprint $table) {
            $table->bigInteger('price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
