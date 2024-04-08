<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBdcServicePriceDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_service_price_default', function (Blueprint $table) {
            $table->integer('progressive_id');
            $table->integer('bdc_price_type_id');
            $table->dropColumn('bdc_price_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_service_price_default', function (Blueprint $table) {
            $table->dropColumn('progressive_id');
            $table->dropColumn('bdc_price_type_id');
            $table->integer('bdc_price_type');
        });
    }
}
