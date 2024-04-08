<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTaskTemplateInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_task_template_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('sub_task_template_id');
            $table->string('title', 255);
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['sub_task_template_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_task_template_infos');
    }
}
