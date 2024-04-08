<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIntoBdcLockCycleNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lock = json_encode(['insert', 'update','delete', 'import']);
        Schema::create('bdc_lock_cycle_name', function (Blueprint $table) use($lock) {
            $table->increments('id');
            $table->integer('bdc_building_id')->nullable();
            $table->integer('cycle_name')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('schedule_active')->nullable();
            $table->string('lock',500)->default($lock);
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('bdc_lock_cycle_name');
    }
}
