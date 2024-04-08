<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNbRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nb_records', function (Blueprint $table) {
            $table->bigIncrements('id')->index('id');
            $table->integer('wallet_id')->index('wallet_id');
            $table->string('category')->comment('Danh mục');
            $table->timestamp('record_date')->comment('Ngày tạo ');
            $table->text('record_description')->nullable()->comment('Mô tả');
            $table->integer('record_type')->comment('Kiểu bản ghi');
            $table->decimal('amount', 20, 2)->comment('Số tiền');
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
        Schema::dropIfExists('nb_records');
    }
}
