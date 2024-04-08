<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostEmotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_emotions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('post_id')->index('post_id');
            $table->enum('post_type', array('article', 'event', 'voucher'))->index('post_type');

            $table->bigInteger('user_id')->index('user_id');
            $table->enum('user_type', array('user', 'customer', 'partner'))->index('user_type');
            $table->string('user_name')->nullable();

            $table->enum('emotion', array('like', 'love', 'haha', 'wow', 'sad', 'angry'))->index('emotion');
            
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
        Schema::dropIfExists('post_emotions');
    }
}
