<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBdcApartmentDebitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_apartment_debit', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('0: Chờ thanh toán, 1: đã thanh toán, 2: quá hạn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_apartment_debit', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
