<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BdcV2LogCoinDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_v2_log_coin_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('bdc_building_id');
            $table->integer('bdc_apartment_id');
            $table->integer('bdc_apartment_service_price_id');
            $table->string('cycle_name')->comment('kỳ');
            $table->integer('coin')->comment('số tiền thanh toán');
            $table->integer('type')->comment('0: trừ coin, 1: cộng coin');
            $table->string('by')->comment('bởi người nào');
            $table->string('note', 255)->comment('ghi chú');
            $table->integer('from_type')->comment('từ nguồn nào, 1: nộp tiền thừa từ bảng reciept, 2: từ ví coin bảng coin');
            $table->integer('from_id')->comment('id từ nguồn đấy');
            $table->text('data')->comment('dữ liệu trước và sau khi thay đổi coin');
            $table->timestamps();
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
        Schema::dropIfExists('bdc_v2_log_coin_detail');
    }
}
