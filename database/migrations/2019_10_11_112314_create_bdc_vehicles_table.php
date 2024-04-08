<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_apartment_id')->comment('id căn hộ');
            $table->string('name',255)->comment('Tên phương tiện');
            $table->string('number',45)->comment('Lưu thông tin biển số phương tiện');
            $table->text('description')->nullable()->comment('Mô tả phương tiện');
            $table->integer('vehicle_category_id')->comment('id cate');
            $table->softDeletes();
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
        Schema::dropIfExists('bdc_vehicles');
    }
}
