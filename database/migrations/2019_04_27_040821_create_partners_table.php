<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePartnersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id', true);
            $table->integer('user_id')->index('user_id');
            $table->string('user_name')->nullable();
            $table->string('name');
            $table->string('company_name');
            $table->string('address')->nullable();
            $table->string('hotline')->nullable();
            $table->text('info')->nullable();
            $table->string('representative')->nullable();
            $table->text('description')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->integer('status')->default(1);
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
        Schema::drop('partners');
    }

}
