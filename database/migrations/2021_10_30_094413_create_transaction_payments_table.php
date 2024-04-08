<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('bdc_apartment_id')->nullable()->comment('căn hộ');
            $table->integer('bdc_receipt_id')->nullable()->comment('phiếu thu');
            $table->string('master_account')->comment('tòa nhà');
            $table->string('virtual_acc')->comment('căn hộ');
            $table->string('payer_name');
            $table->integer('amount');
            $table->text('description');
            $table->uuid('trans_id')->nullable();
            $table->timestamp('trans_record_time')->nullable();
            $table->timestamp('trans_excution_time')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('bank_payment')->nullable();
            $table->string('type')->nullable()->comment('chi_tien: là nộp tiền cho hóa đơn | hoan_tien : xóa phiếu thu nộp tiền');
            $table->tinyInteger('status')->default(0);
            $table->text('note')->nullable();
            $table->text('images')->nullable();
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
        Schema::dropIfExists('transaction_payments');
    }
}
