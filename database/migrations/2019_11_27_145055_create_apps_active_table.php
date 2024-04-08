<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppsActiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps_active', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('app_id', 45);
            $table->string('versions');
            $table->timestamp('public_time');
            $table->tinyInteger('status')->comment('1: active, 2: inactive');
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
        Schema::dropIfExists('apps_active');
    }
}
