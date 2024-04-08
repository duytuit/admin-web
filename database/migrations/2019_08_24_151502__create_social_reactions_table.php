<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_reactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('post_id')->index('social_post_id');
            $table->bigInteger('user_id')->index('user_id');

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
        Schema::dropIfExists('social_reactions');
    }
}
