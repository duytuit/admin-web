<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcBuildingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_building_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bdc_building_id')->default(1)->nullable();
            $table->bigInteger('bdc_department_id')->default(1)->nullable();
            $table->string('title', 45);
            $table->text('description');
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('bdc_request_id')->nullable();
            $table->integer('assign_to');
            $table->text('watchs')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->date('start_at');
            $table->date('end_at');
            $table->integer('related_to')->nullable();
            $table->integer('bdc_apartment_id')->default(1)->nullable();
            $table->text('logs')->nullable();
            $table->text('review_note')->nullable();
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
        Schema::dropIfExists('bdc_building_tasks');
    }
}
