<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_asset_apartments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->comment('tòa nhà');
            $table->integer('bdc_apartment_id')->nullable()->comment('căn hộ');
            $table->string('code')->comment('mã tài sản');
            $table->string('name')->comment('tên tài sản');
            $table->string('description')->nullable()->comment('mô tả');
            $table->text('documents')->nullable()->comment('tài liệu đính kèm');
            
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
        Schema::dropIfExists('asset_apartments');
    }
}
