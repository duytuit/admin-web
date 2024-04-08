<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeIntoBdcServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_services', function (Blueprint $table) {
            $table->integer('partner_id')->nullable()->comment('đối tác');
            $table->integer('price_free')->nullable();
            $table->integer('check_confirm')->nullable();
            $table->integer('persion_register')->nullable();
            $table->integer('service_type')->nullable();
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
            //
        });
    }
}
