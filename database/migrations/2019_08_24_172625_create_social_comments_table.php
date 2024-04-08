<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_comments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('post_id')->index('post_id');
            $table->bigInteger('parent_id')->default(0)->index('parent_id');
            $table->integer('user_id')->index('user_id');
            $table->enum('user_type', array('user', 'customer', 'partner'))->index('user_type');
            $table->text('content')->nullable();
            $table->json('files')->nullable();
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
        Schema::dropIfExists('social_comments');
    }
}
