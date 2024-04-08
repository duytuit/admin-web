<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcPaymentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_payment_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bank_account', 45);
            $table->bigInteger('bdc_building_id')->default(1);
            $table->string('bank_name', 45);
            $table->string('holder_name', 45);
            $table->string('branch', 45);
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
        Schema::dropIfExists('bdc_payment_info');
    }
}
