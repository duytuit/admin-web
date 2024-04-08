<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_apartments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('building_id')->comment('Id Tòa nhà')->default(0);
            $table->string('name',45)->comment('Tên căn hộ');
            $table->text('description')->nullable()->comment('Thông tin mô tả căn hộ');
            $table->tinyInteger('floor')->default(0)->comment('Căn hộ tại tầng số ....');
            $table->tinyInteger('status')->default(0)->comment('Tình trạng căn hộ');
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
        Schema::dropIfExists('bdc_apartments');
    }
}
