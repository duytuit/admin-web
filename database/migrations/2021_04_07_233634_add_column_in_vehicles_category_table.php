<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInVehiclesCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_vehicles_category', function (Blueprint $table) {
            $table->integer('payment_deadline')->nullable();
            $table->integer('bill_date')->nullable();
            $table->integer('service_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles_category', function (Blueprint $table) {
            //
        });
    }
}
