<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBdcElectricMeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_electric_meter', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->comment('tòa nhà');
            $table->integer('bdc_apartment_id')->comment('căn hộ');
            $table->integer('bdc_service_id')->comment('dịch vụ');
            $table->string('cycle_name')->comment('kỳ');
            $table->text('images')->comment('tên ảnh');
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
        Schema::dropIfExists('bdc_electric_meter');
    }
}
