<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBdcMaintenanceAssetIdToBdcBuildingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_building_tasks', function (Blueprint $table) {
            $table->bigInteger('bdc_maintenance_asset_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_building_tasks', function (Blueprint $table) {
            $table->dropColumn('bdc_maintenance_asset_id');
        });
    }
}
