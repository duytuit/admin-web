<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBdcDebitDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('bdc_price_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_debit_detail', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('price');
            $table->dropColumn('bdc_price_type_id');
        });
    }
}
