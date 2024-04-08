<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPriceCurrentIntoBdcDebitDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            $table->string('price_current')->nullable()->comment('giá lúc thời điểm tính');
            $table->text('image')->nullable()->comment('ảnh chỉ số điện nước');
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
