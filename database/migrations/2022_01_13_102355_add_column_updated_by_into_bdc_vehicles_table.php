<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUpdatedByIntoBdcVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_vehicles', function (Blueprint $table) {
            $table->integer('updated_by')->nullable()->comment('người cập nhật cuối cùng');
            $table->date('finish')->nullable()->comment('ngày kết thúc tính phí');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_vehicles', function (Blueprint $table) {
            //
        });
    }
}
