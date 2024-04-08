<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcSentStatusDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_sent_status_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sent_status_id')->comment('id cua bang sent_status');
            $table->text('contact')->comment('thong tin lien he: email or sdt or app_id');
            $table->enum('status', ['true', 'false'])->comment('trang thai gui tin');
            
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
        Schema::dropIfExists('bdc_mails_status_detail');
    }
}
