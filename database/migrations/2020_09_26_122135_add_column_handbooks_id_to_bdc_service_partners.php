<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnHandbooksIdToBdcServicePartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_service_partners', function (Blueprint $table) {
              $table->integer('bdc_handbook_id')->index('bdc_handbook_id')->nullable()->unsigned();
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
             $table->dropColumn('bdc_handbook_id')->index('bdc_handbook_id');
        });
    }
}
