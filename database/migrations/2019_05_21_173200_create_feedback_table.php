<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->increments('id', true);
            $table->bigInteger('customer_id')->index('customer_id');
            $table->enum('type', array('user', 'product', 'service', 'other'))->default('other')->index('type');
            $table->string('title');
            $table->text('content')->nullable();
            $table->integer('rating')->nullable();
            $table->json('attached')->nullable();
            $table->boolean('status')->default(1)->index('status');
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
        Schema::dropIfExists('feedback');
    }
}
