<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeToBdcCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_customers', function (Blueprint $table) {
              $table->string('note_confirm',500)->nullable();
              $table->string('date_handover')->nullable();
              $table->integer('status_confirm')->nullable()->unsigned()->comment('1:Chưa đủ điều kiện, 2: Đủ điều kiện, 3:Đã gửi thông báo, 4:Đã xác nhận, 5:Đã hủy, 6: Đã bàn giao');
              $table->integer('status_success_handover')->nullable()->unsigned()->comment('1:Đã bào giao, 2:Chưa bàn giao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_customers', function (Blueprint $table) {
            //
        });
    }
}
