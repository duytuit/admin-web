<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeColumnBdcElectricMeterIntoBdcElectricMeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('bdc_electric_meter')->truncate();
        Schema::table('bdc_electric_meter', function (Blueprint $table) {
            $table->renameColumn('cycle_name', 'month_create')->comment('kỳ chốt số');
            $table->renameColumn('chi_so_dau', 'before_number')->comment('chỉ số đầu');
            $table->renameColumn('chi_so_cuoi', 'after_number')->comment('chỉ số cuối');
            $table->renameColumn('user_id', 'created_by')->comment('người tạo');
            $table->tinyInteger('type_action')->default(0)->comment('thay đổi đồng hồ đo chỉ số');
            $table->tinyInteger('status')->default(1)->comment('trạng thái');
            $table->dateTime('date_update')->comment('ngày cập nhật chỉ số');
            $table->dropColumn('bdc_service_id');
            $table->dropColumn('type');
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
           
        });
    }
}
