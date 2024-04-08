<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAttachFileToBdcMaintenanceAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_maintenance_assets', function (Blueprint $table) {
            $table->text('domain')->after('description')->nullable();
            $table->text('attach_file')->after('domain')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_maintenance_assets', function (Blueprint $table) {
            //
        });
    }
}
