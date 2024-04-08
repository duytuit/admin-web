<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcServicePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_service_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bdc_service_id');
            $table->integer('bdc_price_type_id');
            $table->string('from')->default(0)->comment('Giá từ bao nhiêu');
            $table->string('to')->default(0)->comment('Giá đến bao nhiêu');
            $table->string('unit_price')->comment('Giá thành tiền');
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
        Schema::dropIfExists('bdc_service_price');
    }
}
