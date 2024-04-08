<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAttributePriceIntoBdcProgressivePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_progressive_price', function (Blueprint $table) {
            $table->float('price')->change();
        });
    }

    /**
     * Reverse the migrations.
    
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_progressive_price', function (Blueprint $table) {
            //
        });
    }
}
