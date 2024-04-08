<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIntoBdcAccountingAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_accounting_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('bdc_building_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->tinyInteger('tai_khoan_no_pt')->default(0);
            $table->tinyInteger('tai_khoan_co_pt')->default(0);
            $table->tinyInteger('tai_khoan_no_bao_co')->default(0);
            $table->tinyInteger('tai_khoan_co_bao_co')->default(0);
            $table->tinyInteger('tai_khoan_co_thue')->default(0);
            $table->tinyInteger('tai_khoan_no_thue')->default(0);
            $table->tinyInteger('tai_khoan_co_truoc_vat')->default(0);
            $table->tinyInteger('tai_khoan_no_truoc_vat')->default(0);
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
        Schema::table('bdc_accounting_accounts', function (Blueprint $table) {
            //
        });
    }
}
