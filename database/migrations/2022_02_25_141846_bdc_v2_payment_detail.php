<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BdcV2PaymentDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_v2_payment_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_apartment_id');
            $table->integer('bdc_apartment_service_price_id');
            $table->string('cycle_name')->comment('kỳ');
            $table->integer('bdc_receipt_id')->nullable()->comment('kênh lập phiếu thu');
            $table->integer('bdc_log_coin_id')->nullable()->comment('kênh tiền thừa, chỉ định hoạch toán');
            $table->integer('bdc_debit_detail_id')->comment('Thanh toán cho công nợ nào');
            $table->string('paid')->comment('số tiền thanh toán');
            $table->dateTime('paid_date')->comment('ngày thanh toán, ngày này sẽ quyết định nó thuộc kỳ nào');
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_v2_payment_detail');
    }
}
