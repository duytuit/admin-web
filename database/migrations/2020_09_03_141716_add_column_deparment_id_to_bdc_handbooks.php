<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeparmentIdToBdcHandbooks extends Migration
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
             $table->integer('department_id')->index('department_id')->nullable()->unsigned();
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
            $table->dropColumn('department_id')->index('department_id')->nullable()->unsigned();
        });
    }
}
