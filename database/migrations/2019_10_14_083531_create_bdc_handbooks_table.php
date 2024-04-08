<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcHandbooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_handbooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bdc_building_id', 45)->nullable();
            $table->string('title', 45)->comment('Ten cam nang');
            $table->text('content')->comment('Noi dung cam nang');
            $table->integer('bdc_handbook_category_id')->comment('Loai cam nang');
            $table->integer('bdc_handbook_type_id')->comment('Kieu cam nang');
            $table->tinyInteger('status')->comment('trang thai cam nang');
            $table->integer('pub_profile_id')->comment('id cua nguoi dung');
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
        Schema::dropIfExists('bdc_handbooks');
    }
}
