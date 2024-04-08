<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BdcV2DebitDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_v2_debit_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_building_id')->index('bdc_building_id');
            $table->integer('bdc_bill_id')->index('bdc_bill_id');
            $table->integer('bdc_apartment_id');
            $table->integer('bdc_apartment_service_price_id');

            $table->date('from_date')->comment('tính từ ngày');
            $table->date('to_date')->comment('tính tới ngày');
            $table->text('detail')->comment('chi tiết kiểu tính ví dụ điện, nước các nấc');

            $table->integer('previous_owed')->comment('nợ trước');

            $table->string('cycle_name')->comment('kỳ');

            $table->string('quantity')->comment('số lượng sử dụng');
            $table->integer('price')->comment('đơn giá');

            $table->integer('sumery')->comment('tổng tiền cần phải thanh toán');

            $table->integer('discount')->nullable()->comment('số tiền giảm giá');
            $table->integer('discount_type')->nullable()->comment('1: loại giảm giá % hay 0: số tiền cố định,');
            $table->string('discount_note', 255)->nullable()->comment('lý do giảm giá');

            $table->integer('paid')->default(0)->comment('số tiền thực tế đã trả theo đơn');
            $table->integer('paid_by_cycle_name')->default(0)->comment('số tiền thực tế đã trả theo kỳ');
            $table->integer('before_cycle_name')->default(0)->comment('số tiền đầu kỳ');
            $table->integer('after_cycle_name')->default(0)->comment('số tiền cuối kỳ');
            $table->integer('deleted_by')->comment('người xóa');

            $table->string('image', 255)->nullable()->comment('url ảnh số điện nước');

            $table->timestamps();
            $table->softDeletes();
            // create index
            $table->unique(['bdc_apartment_id','bdc_apartment_service_price_id','cycle_name'],'index_partment_service_cycle_name');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_v2_debit_detail');
    }
}
