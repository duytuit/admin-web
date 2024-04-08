<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDiscountIntoBdcDebitDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            $table->integer('price_after_discount')->default(0)->comment('số tiền sau khi giảm giá');
            $table->string('type_discount')->nullable()->comment('kiểu giảm giá');
            $table->integer('discount')->nullable()->comment('tỷ lệ giảm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            //
        });
    }
}
