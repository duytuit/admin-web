<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReceiptsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->text('url_payment')->nullable();
            $table->string('vnp_bank_code',45)->nullable();
            $table->string('vnp_banktranno',255)->nullable();
            $table->string('vnp_cardtype',20)->nullable();
            $table->string('vnp_paydate',15)->nullable();
            $table->string('vnp_transactionno',15)->nullable();
            $table->string('vnp_responsecode',2)->nullable();
            $table->string('vnp_currcode',3)->nullable();
            $table->integer('vnp_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
         Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->dropColumn('url_payment');
            $table->dropColumn('vnp_bank_code');
            $table->dropColumn('vnp_banktranno');
            $table->dropColumn('vnp_cardtype');
            $table->dropColumn('vnp_paydate');
            $table->dropColumn('vnp_transactionno');
            $table->dropColumn('vnp_responsecode');
            $table->dropColumn('vnp_currcode');
            $table->dropColumn('vnp_status');
        });
    }
}
