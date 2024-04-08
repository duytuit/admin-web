<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcSentStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_sent_status', function (Blueprint $table) {
            $table->increments('id');
            $table->text('category')->comment('loai tin duoc gui di: mail, sms, sent_app');
            $table->text('type')->comment('cac loai nhu: post, bill,...');
            $table->integer('sent_id')->comment('id cua post gui neu co');
            $table->integer('building_id')->comment('id cua toa nha');
            $table->integer('total')->default(0)->comment('tong in phai gui');
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
        Schema::dropIfExists('bdc_notification');
    }
}
