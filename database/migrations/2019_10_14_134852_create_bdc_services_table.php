<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_period_id');
            $table->string('name');
            $table->text('description');
            $table->string('unit');
            $table->integer('bill_date');
            $table->integer('payment_deadline');
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
        Schema::dropIfExists('bdc_services');
    }
}
