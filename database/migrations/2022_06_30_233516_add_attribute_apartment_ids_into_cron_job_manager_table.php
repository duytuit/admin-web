<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeApartmentIdsIntoCronJobManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_job_manager', function (Blueprint $table) {
            $table->text('apartment_ids')->nullable()->comment('danh sách căn hô');
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
