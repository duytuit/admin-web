<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcProvisionalReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_provisional_receipt', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_apartment_id');
            $table->integer('config_id');
            $table->string('name');
            $table->string('receipt_code');
            $table->string('payment_type');
            $table->string('type');
            $table->integer('price');
            $table->text('description');
            $table->integer('status');
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
        Schema::dropIfExists('bdc_provisional_receipt');
    }
}
