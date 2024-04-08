<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('building_id')->comment('id tòa nhà');
            $table->string('name',255)->comment('Tên file');
            $table->string('type',45)->comment('loại');
            $table->string('url',255)->comment('URL');
            $table->text('description')->comment('mô tả');
            $table->string('more_type',255);
            $table->string('more_id',255);
            $table->tinyInteger('status')->comment('loại');
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
        Schema::dropIfExists('system_files');
    }
}
