<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_apartment_id')->index('bdc_apartment_id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->string('bill_code');
            $table->integer('cost');
            $table->string('customer_name');
            $table->string('customer_address');
            $table->string('provider_address');
            $table->date('deadline');
            $table->integer('is_vat');
            $table->integer('status');
            $table->integer('notify');
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
        Schema::dropIfExists('bdc_bills');
    }
}
