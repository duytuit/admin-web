<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnByIntoBdcElectricMeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_electric_meter', function (Blueprint $table) {
            $table->integer('chi_so_dau')->default(0)->comment('chỉ số đầu');
            $table->integer('chi_so_cuoi')->default(0)->comment('chỉ số cuối');
            $table->string('type')->nullable()->comment('kiểu');
            $table->string('user_id')->nullable()->comment('Người tạo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_electric_meter', function (Blueprint $table) {
            //
        });
    }
}
