<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchanges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cb_id');
            $table->string('name');
            $table->string('hotline');
            $table->string('city');
            $table->string('district');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default(1);
            $table->string('user_id')->nullable();
            $table->json('location')->nullable();
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
        Schema::dropIfExists('exchanges');
    }
}
