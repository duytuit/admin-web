<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomerRatedServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_customer_rated_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_name')->nullable()->comment('tên khách hàng');
            $table->string('email')->nullable()->comment('email khách hàng');
            $table->string('phone')->nullable()->comment('điện thoại khách hàng');
            $table->string('apartment_name')->nullable()->comment('căn hộ khách hàng');
            $table->string('rated')->nullable()->comment('kem, tot, kha, trung_binh, yeu, xuat_sac');
            $table->string('description')->nullable();
            $table->integer('employee_id')->nullable()->comment('mã nhân viên');
            $table->integer('department_id')->nullable()->comment('mã bộ phận');
            $table->integer('bdc_building_id')->nullable()->comment('tòa nhà');
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
        Schema::dropIfExists('bdc_customer_rated_services');
    }
}
