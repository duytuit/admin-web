<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('building_id');
            $table->integer('bdc_department_id')->index();
            $table->integer('task_category_id')->index();
            $table->integer('work_shift_id')->index();
            $table->string('task_name', 250)->index();
            $table->text('description');
            $table->string('priority', 15)->nullable();
            $table->string('created_by', 30);
            $table->timestamp('due_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('completed_on')->nullable();
            $table->string('type', 15);
            $table->string('status', 15);
            $table->string('supervisor', 30);
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
        Schema::dropIfExists('tasks');
    }
}
