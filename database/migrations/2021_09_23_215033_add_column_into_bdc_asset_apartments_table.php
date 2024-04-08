<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIntoBdcAssetApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_asset_apartments', function (Blueprint $table) {
            $table->integer('building_place_id')->nullable()->comment('tòa nhà');
            $table->integer('asset_category_id')->nullable()->comment('loại tài sản');
            $table->integer('created_by')->nullable()->comment('người tạo');
            $table->integer('updated_by')->nullable()->comment('người cập nhật gần nhất');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('bdc_asset_apartments', function (Blueprint $table) {
        //     //
        // });
    }
}
