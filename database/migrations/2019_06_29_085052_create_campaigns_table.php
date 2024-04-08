<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->bigIncrements('id', true);
			$table->bigInteger('user_id')->index('user_id')->nullable()->comment('ID nhân viên nhập');
			$table->bigInteger('project_id')->index('project_id')->nullable()->comment('ID dự án');
			$table->string('title')->nullable()->comment('Tên chiến dịch');
			$table->json('file_user_cus')->nullable()->comment('Fiel nhân viên');
			$table->integer('sum_user')->nullable()->comment('Số lượng nhân viên');
			$table->integer('sum_customer')->nullable()->comment('Số lượng khách hàng');
			$table->boolean('feedback')->default(0)->comment('Số lượng phản hồi');
			$table->integer('status')->default(0)->comment('Số lượng quan tâm');
			$table->text('description')->nullable()->comment('Ghi chú');
			$table->string('source')->nullable()->comment('Nguồn nhập');
			$table->integer('diary_id')->nullable()->comment('ID nhật ký');
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
        Schema::dropIfExists('campaigns');
    }
}
