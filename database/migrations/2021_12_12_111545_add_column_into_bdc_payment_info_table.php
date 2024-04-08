<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIntoBdcPaymentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_payment_info', function (Blueprint $table) {
            $table->tinyInteger('app_status')->default(0);
            $table->tinyInteger('web_status')->default(0);
            $table->integer('user_id')->nullable();
            $table->string('code')->nullable();
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
