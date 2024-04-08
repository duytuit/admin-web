<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnIntoBdcBuildingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_building', function (Blueprint $table) {
            $table->renameColumn('vi_viet_merchant_id', 'merchant_9p_id');
            $table->renameColumn('vi_viet_access_code', 'partnert_9p_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_building', function (Blueprint $table) {
            //
        });
    }
}
