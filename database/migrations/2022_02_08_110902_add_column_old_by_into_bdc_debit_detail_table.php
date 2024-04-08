<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOldByIntoBdcDebitDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            $table->tinyInteger('old')->default(1)->comment('1:dữ liệu cũ 0:là dữ liệu mới');
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
