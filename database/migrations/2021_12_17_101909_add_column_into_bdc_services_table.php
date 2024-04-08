<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIntoBdcServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_services', function (Blueprint $table) {
            $table->integer('ngay_chuyen_doi')->nullable();
            $table->integer('index_accounting')->nullable()->comment('thứ tự ưu tiên hạch toán');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_services', function (Blueprint $table) {
            //
        });
    }
}
