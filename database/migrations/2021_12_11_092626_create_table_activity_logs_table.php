<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('bdc_building_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('module')->nullable();
            $table->string('module_id',5)->nullable();
            $table->string('sub_module')->nullable();
            $table->string('sub_module_id',5)->nullable();
            $table->string('log_type')->nullable();
            $table->longText('description')->nullable();
            $table->ipAddress('ip');
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
        Schema::dropIfExists('activity_logs');
    }
}
