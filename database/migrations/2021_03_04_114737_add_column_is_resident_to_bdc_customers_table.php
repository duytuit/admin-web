<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsResidentToBdcCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_customers', function (Blueprint $table) {
            $table->integer('is_resident')->nullable()->unsigned()->comment('null:Là cư dân, 0 :Chưa là cư dân');
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
