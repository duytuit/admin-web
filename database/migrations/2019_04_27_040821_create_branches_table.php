<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBranchesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function(Blueprint $table)
        {
            $table->increments('id', true);
            $table->integer('user_id')->index('user_id');
            $table->string('user_name')->nullable();
            $table->integer('partner_id')->nullable();
            $table->string('partner_name', 191)->nullable();
            $table->string('title');
            $table->string('hotline')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->text('info')->nullable();
            $table->string('representative')->nullable();
            $table->integer('status')->nullable();
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
        Schema::drop('branches');
    }

}
