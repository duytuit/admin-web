<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeIntoBdcProgressivePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_progressive_price', function (Blueprint $table) {
            $table->string('option')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('period_quantity')->nullable();
            $table->integer('date_quantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_progressive_price', function (Blueprint $table) {
            //
        });
    }
}
