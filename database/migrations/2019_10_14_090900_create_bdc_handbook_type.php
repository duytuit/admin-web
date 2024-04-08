<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcHandbookType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_handbook_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bdc_building_id', 45)->nullable();
            $table->string('name', 255)->comment('ten kieu cam nang');
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
        Schema::dropIfExists('bdc_handbook_type');
    }
}
