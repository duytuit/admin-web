<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->integer('bdc_apartment_id')->index('bdc_apartment_id');
            $table->string('receipt_code');
            $table->integer('cost');
            $table->string('customer_name');
            $table->string('customer_address');
            $table->string('provider_address');
            $table->string('bdc_receipt_total');
            $table->text('logs');
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
        Schema::dropIfExists('bdc_receipts');
    }
}
