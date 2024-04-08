<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIntoBdcReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->integer('tai_khoan_no')->nullable();
            $table->integer('tai_khoan_co')->nullable();
            $table->integer('ngan_hang')->nullable();
            $table->string('ma_khach_hang')->nullable();
            $table->string('ten_khach_hang')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            //
        });
    }
}
