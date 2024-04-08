<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldStatusSuccessHandoverToBdcCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_customers', function (Blueprint $table) {
            $table->integer('status_success_handover')->default(0)->comment('1:Đã bào giao, 0:Chưa bàn giao')->change();
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
