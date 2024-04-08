<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBdcDepartment1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
   Schema::table('bdc_department', function (Blueprint $table) {
            $table->integer('bdc_building_place_id')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
         Schema::table('bdc_department', function (Blueprint $table) {
            $table->dropColumn('bdc_building_place_id');
        });
    }
}
