<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIntroInBdcHandbooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_handbooks', function (Blueprint $table) {
            //
             $table->tinyInteger('feature')->default(1)->comment('0: giới thiệu, 1: bài viết');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_handbooks', function (Blueprint $table) {
            //
             $table->dropColumn('feature');
        });
    }
}
