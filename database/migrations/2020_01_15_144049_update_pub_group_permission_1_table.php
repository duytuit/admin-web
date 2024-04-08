<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePubGroupPermission1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::table('pub_group_permission', function (Blueprint $table) {
            $table->integer('bdc_building_id')->nullable()->default(0);
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
         Schema::table('pub_group_permission', function (Blueprint $table) {
            $table->dropColumn('bdc_building_id');
        });
    }
}
