<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcDebitLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_debit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->nullable();
            $table->integer('bdc_apartment_id')->nullable();
            $table->integer('bdc_service_id')->nullable();
            $table->string('key')->nullable();
            $table->text('input')->nullable();
            $table->text('data')->nullable();
            $table->string('message')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('bdc_debit_logs');
    }
}
