<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcDebitDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_debit_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->integer('bdc_bill_id')->index('bdc_bill_id');
            $table->integer('bdc_apartment_id')->index('bdc_apartment_id');
            $table->integer('bdc_service_id')->index('bdc_service_id');
            $table->integer('bdc_apartment_service_price_id')->index('bdc_apartment_service_price_id');
            $table->string('title');
            $table->integer('sumery');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('detail');
            $table->integer('version');
            $table->integer('new_sumery');
            $table->integer('previous_owed');
            $table->integer('paid');
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
        Schema::dropIfExists('bdc_debit_detail');
    }
}
