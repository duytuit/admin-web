<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVehicleRelate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_progressive_price', function (Blueprint $table) {
            $table->tinyInteger('priority_level')->nullable();
        });

        Schema::table('bdc_vehicles', function (Blueprint $table) {
            $table->integer('bdc_progressive_price_id')->nullable();
            $table->date('first_time_active')->nullable();
            $table->boolean('status');
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
