<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcHandbookCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_handbook_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bdc_building_id', 45)->nullable();
            $table->string('name', 255)->comment('ten loai cam nang');
            $table->string('parent_id', 45)->comment('id cua cam nang cha');
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
        Schema::dropIfExists('bdc_handbook_category');
    }
}
