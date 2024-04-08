<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCheckAllApartmentCronJobManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_job_manager', function (Blueprint $table) {
            $table->integer('check_all_apartment')->nullable()->comment('Check all căn hộ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cron_job_manager', function (Blueprint $table) {
            //
        });
    }
}
