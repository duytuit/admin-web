<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInfoCustomersBdcServicePartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_service_partners', function (Blueprint $table) {
            $table->string('customer')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_service_partners', function (Blueprint $table) {
             $table->dropColumn('customer');
             $table->dropColumn('phone');
             $table->dropColumn('email');
        });
    }
}
