<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNbWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nb_wallet', function (Blueprint $table) {
            $table->bigIncrements('id')->index('id');
            $table->bigInteger('user_id')->index('user_id');
            $table->text('wallet_name')->comment('Tên tài khoản');
            $table->string('currency_code')->comment('Đơn vị tiền tệ');
            $table->text('wallet_description')->nullable()->comment('Mô tả');
            $table->decimal('wallet_balance', 20, 2)->comment('Số dư');
            $table->boolean('save_to_report')->default(0)->comment('Lưu vào báo cáo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nb_wallet');
    }
}
