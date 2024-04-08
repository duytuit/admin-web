<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetHandOversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_asset_hand_overs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->nullable()->comment('tòa nhà');
            $table->string('asset_apartment_id')->nullable()->comment('mã tài sản');
            $table->string('apartment_id')->nullable()->comment('mã căn hộ');
            $table->string('handover_person_id')->nullable()->comment('người bàn giao');

            $table->timestamp('date_expected')->nullable()->comment('ngày dự kiến');
            $table->timestamp('date_of_delivery')->nullable()->comment('ngày bàn giao');

            $table->integer('warranty_period')->nullable()->comment('thời gian bảo hành');

            $table->string('customer')->nullable()->comment('khách hàng');

            $table->string('email')->nullable()->comment('email');
            $table->string('phone')->nullable()->comment('phone');

            $table->string('description')->nullable()->comment('ghi chú');

            $table->text('documents')->nullable()->comment('tài liệu đính kèm');

            $table->tinyInteger('status')->nullable()->comment('trạng thái');

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
        Schema::dropIfExists('asset_hand_overs');
    }
}
