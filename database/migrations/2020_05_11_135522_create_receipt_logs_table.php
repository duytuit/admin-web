<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->nullable();
            $table->string('bill_id')->nullable();
            $table->string('bill_code')->nullable();
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
        Schema::dropIfExists('receipt_logs');
    }
}
