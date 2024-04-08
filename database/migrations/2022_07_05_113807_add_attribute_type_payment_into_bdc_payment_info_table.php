<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeTypePaymentIntoBdcPaymentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_payment_info', function (Blueprint $table) {
            $table->tinyInteger('type_payment')->nullable();
            $table->string('short_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_payment_info', function (Blueprint $table) {
            //
        });
    }
}
