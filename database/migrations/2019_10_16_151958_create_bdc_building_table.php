<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBuildingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_building', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description');
            $table->string('address');
            $table->string('phone', 45);
            $table->string('email', 45);
            $table->string('vnp_merchant_id', 45)->nullable();
            $table->string('vnp_secret', 45)->nullable();
            $table->string('vi_viet_merchant_id', 45)->nullable();
            $table->string('vi_viet_access_code', 45)->nullable();
            $table->string('vi_viet_secret', 45)->nullable();
            $table->string('vi_viet_agent_id', 45)->nullable();
            $table->integer('company_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdc_building');
    }
}
