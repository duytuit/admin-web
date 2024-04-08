<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnBankNameInBdcPaymentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_payment_info', function (Blueprint $table) {
            $table->string('bank_account',450)->change();
            $table->string('bank_name',450)->change();
            $table->string('holder_name',450)->change();
            $table->string('branch',450)->change();
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
