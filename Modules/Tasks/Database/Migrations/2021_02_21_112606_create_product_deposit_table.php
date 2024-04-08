<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDepositTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_deposit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->comment('tên căn hộ gửi bán');
            $table->string('address')->nullable()->comment('Địa chỉ căn sản phẩm gửi bán');
            $table->longText('description')->nullable()->comment('Ghi chú');
            $table->string('direction')->nullable()->comment('Hướng');
            $table->tinyInteger('type')->nullable()->comment('Loại hình bất động sản');
            $table->tinyInteger('needed')->nullable()->comment('nhu cầu');
            $table->decimal('acreage', 10, 2)->nullable()->comment('Loại hình bất động sản');
            $table->decimal('price', 15, 3)->nullable()->comment('giá bán');
            $table->json('images')->nullable()->comment('Hình ảnh sản phẩm');
            $table->date('begin_date')->nullable()->comment('thời gian bắt đầu');
            $table->tinyInteger('status')->nullable()->comment('trạng thái')->default(1);
            $table->tinyInteger('status_deposit')->nullable()->comment('trạng thái');
            $table->string('product_code_real')->nullable()->comment('Mã căn thực tế');
            $table->integer('user_id')->default(0)->nullable()->comment('id khách hàng');
            $table->integer('product_id')->default(0)->nullable()->comment('id sản phẩm');
            $table->tinyInteger('handover_status')->nullable()->comment("Tình trạng bàn giao: 1-Đầy đủ nội thất| 2-Cơ bản| 3-Thô");
            $table->integer('contract_period')->nullable()->comment("Thời hạn hợp đồng");
            $table->date('contract_date')->nullable()->comment("Ngày hết hạn hợp đồng");
            $table->json('legal_photo')->nullable()->comment("Ảnh pháp lý");
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
        Schema::dropIfExists('table_product_deposit');
    }
}
