<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("title");
            $table->string("description");
            $table->bigInteger("user_id");
            $table->integer("bdc_building_id");
            $table->longText("attach_file");
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
        Schema::dropIfExists('bdc_documents');
    }
}
