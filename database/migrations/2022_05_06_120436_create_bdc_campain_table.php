<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcCampainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_campain', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->text('type')->comment('cac loai nhu: post, bill,...');
            $table->integer('type_id')->nullable()->comment('id cua post gui neu co');
            $table->integer('bdc_building_id')->nullable()->comment('id cua toa nha');
            $table->json('total')->comment('tổng thông báo phải gửi');
            $table->json('status');
            $table->integer('send')->comment('0:chưa gửi ,1: đã gửi');
            $table->integer('sort')->comment('hàng chờ ưu tiên');
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
        Schema::dropIfExists('bdc_campain');
    }
}
