<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcDocumentHasType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_document_has_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bdc_document_id');
            $table->integer("bdc_document_type");
            $table->integer("bdc_document_type_id");
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
        Schema::dropIfExists('bdc_document_has_type');
    }
}
