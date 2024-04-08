<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBdcPaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_payment_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('bdc_building_id')->nullable();
            $table->integer('bdc_apartment_id')->nullable();
            $table->integer('bdc_receipt_id')->nullable();
            $table->integer('bdc_service_id')->nullable();
            $table->integer('bdc_bill_id')->nullable();
            $table->integer('bdc_debit_detail_id')->nullable();
            $table->string('cost')->nullable()->comment('số tiền thanh toán');
            $table->string('type_payment')->nullable()->comment('tien_mat | chuyen_khoan');
            $table->string('type')->nullable()->comment('phieu_thu | phieu_thu_truoc | phieu_ke_toan | phieu_bao_co');
            $table->integer('bdc_ncc_id')->nullable()->comment('Áp dụng với phí tiện ích khác');
            $table->string('tk_no')->nullable();
            $table->string('tk_co')->nullable();
            $table->integer('no_phat_sinh')->nullable();
            $table->integer('co_phat_sinh')->nullable();
            $table->integer('dau_ky')->nullable();
            $table->integer('cuoi_ky')->nullable();
            $table->string('cycle_name')->nullable()->comment('kỳ công nợ');
            $table->dateTime('create_date')->nullable()->comment('thời gian hạch toán');
            $table->text('metadata')->nullable()->comment('trường đồng bộ với các hệ thống kế toán khác');
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
        Schema::dropIfExists('bdc_payment_details');
    }
}
