<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcProgressivePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_progressive_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('from');
            $table->integer('to');
            $table->integer('price');
            $table->integer('progressive_id');
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
        Schema::dropIfExists('bdc_progressive_price');
    }
}
