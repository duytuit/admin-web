<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTransactionAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_transaction_accounting', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bdc_building_id')->nullable();
            $table->integer('bdc_apartment_id')->nullable();
            $table->longText('detail')->nullable();
            $table->string('customer_name')->nullable()->comment('Khách hàng');
            $table->string('customer_address')->nullable()->comment('Địa chỉ khách hàng');
            $table->integer('cost')->nullable()->comment('sô tiền hạch toán');
            $table->integer('tk_no')->nullable();
            $table->integer('tk_co')->nullable();
            $table->string('ma_khach_hang')->nullable();
            $table->string('ten_khach_hang')->nullable();
            $table->integer('ngan_hang')->nullable();
            $table->text('description')->nullable();
            $table->string('type_payment')->nullable()->comment('chuyển khoản | tiền mặt');
            $table->string('type')->nullable()->comment('phieu_thu | phieu_thu_truoc | phieu_ke_toan | phieu_bao_co');
            $table->string('message')->nullable()->comment('thông báo');
            $table->integer('account_balance')->nullable()->comment('tiền thừa');
            $table->string('status')->nullable()->comment('da_hach_toan | cho_hach_toan | chua_hop_le');
            $table->dateTime('create_date')->nullable()->comment('thời gian hạch toán');
            $table->dateTime('confirm_date')->nullable()->comment('thời gian xác nhận');
            $table->integer('user_confirm')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('history_transaction_accounting');
    }
}
