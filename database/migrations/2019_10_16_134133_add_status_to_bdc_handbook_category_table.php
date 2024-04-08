<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToBdcHandbookCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_handbook_category', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('trang thai danh muc');
            $table->tinyInteger('bdc_handbook_type_id')->default(1)->comment('phan loai danh muc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_handbook_category', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
