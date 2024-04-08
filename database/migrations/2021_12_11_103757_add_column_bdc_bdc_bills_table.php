<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBdcBdcBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_bills', function (Blueprint $table) {
            $table->integer('bdc_ncc_id')->nullable()->comment('Áp dụng với phí tiện ích khác');
            $table->integer('no_cu')->nullable();
            $table->text('metadata')->nullable()->comment('trường đồng bộ với các hệ thống kế toán khác');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_bills', function (Blueprint $table) {
            //
        });
    }
}
