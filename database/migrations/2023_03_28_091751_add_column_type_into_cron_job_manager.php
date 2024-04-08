<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeIntoCronJobManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_job_manager', function (Blueprint $table) {
            $table->integer('type')->nullable()->comment('1: là import điện nước từ chỉ số điện nước, null : import điện nước trực tiếp hoặc import dịch vụ lũy tiến');
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
