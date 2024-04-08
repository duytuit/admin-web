<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBdcAccountingVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_accounting_vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('bdc_building_id')->nullable();
            $table->integer('bdc_apartment_id')->nullable();
            $table->integer('bdc_receipt_id')->nullable();
            $table->integer('bdc_apartment_service_price_id')->nullable();
            $table->integer('bdc_service_id')->nullable();
            $table->text('bdc_bill')->nullable();
            $table->integer('bdc_debit_detail_id')->nullable();
            $table->string('cost_paid')->nullable()->comment('sô tiền hạch toán');
            $table->string('tk_no')->nullable();
            $table->string('tk_co')->nullable();
            $table->string('type_payment')->nullable()->comment('tien_thua | hach_toan');
            $table->string('cycle_name')->nullable()->comment('kỳ công nợ');
            $table->string('type')->nullable()->comment('phieu_thu | phieu_thu_truoc | phieu_ke_toan | phieu_bao_co');
            $table->dateTime('create_date')->nullable()->comment('thời gian hạch toán');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_accounting_vouchers');
    }
}
