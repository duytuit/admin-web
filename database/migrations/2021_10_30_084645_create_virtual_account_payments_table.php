<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVirtualAccountPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_account_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('bdc_building_id')->comment('tòa nhà');
            $table->integer('bdc_apartment_id')->comment('căn hộ');
            $table->string('virtual_acc_id')->nullable();
            $table->string('virtual_acc_no')->nullable();
            $table->string('virtual_acc_name')->nullable();
            $table->string('virtual_acc_mobile')->nullable();
            $table->string('virtual_alt_key')->nullable();
            $table->string('open_date')->comment('ngày mở');
            $table->string('value_date')->comment('');
            $table->string('expiry_date')->comment('ngày hết hạn');
            $table->tinyInteger('status')->default(1)->comment('trạng thái');
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
        Schema::dropIfExists('virtual_account_payments');
    }
}
