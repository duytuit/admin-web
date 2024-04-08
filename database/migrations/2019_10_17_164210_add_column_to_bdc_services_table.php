<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBdcServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_services', function (Blueprint $table) {
            $table->integer('company_id');
            $table->string('service_code');
            $table->tinyInteger('status')->default(1)->comment('0: không dùng, 1: dùng');
            $table->float('price');
            $table->date('first_time_active')->nullable();
            $table->tinyInteger('type')->default(0)->comment('0: không phải phương tiện, 1: phương tiện');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_services', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('service_code');
            $table->dropColumn('status');
            $table->dropColumn('price');
            $table->dropColumn('first_time_active');
            $table->dropColumn('type');
        });
    }
}
