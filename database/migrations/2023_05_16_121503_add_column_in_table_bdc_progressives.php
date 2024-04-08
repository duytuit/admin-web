<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInTableBdcProgressives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('UPDATE bdc_progressives SET applicable_date = created_at');
        \Illuminate\Support\Facades\DB::statement('UPDATE bdc_progressives INNER JOIN bdc_service_price_default on bdc_progressives.id = bdc_service_price_default.progressive_id
        SET bdc_progressives.bdc_service_id = bdc_service_price_default.bdc_service_id');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_progressives', function (Blueprint $table) {
            //
        });
    }
}
