<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBusinessPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_business_partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('mobile');
            $table->string('address',256)->nullable();
            $table->string('contact')->nullable()->comment('người liên hệ');
            $table->string('email')->nullable();
            $table->string('representative')->nullable()->comment('người đại diện');
            $table->string('position')->nullable()->comment('chức vụ');
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('bdc_building_id');
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
        Schema::dropIfExists('bdc_business_partners');
    }
}
