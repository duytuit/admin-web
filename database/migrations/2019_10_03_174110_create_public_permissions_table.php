<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_vietnamese_ci';
            $table->bigIncrements('id');
            $table->integer('module_id')->index('module_id');
            $table->string('route_name', 254)->nullable()->index('route_name');
            $table->string('title', 254)->nullable();
            $table->string('link', 254)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('has_menu')->default(0);
            $table->text('description', 1000)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('pub_permissions');
    }
}
