<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->bigIncrements('id');

            // user_id
            $table->integer('user_id')->index('user_id');

            //common
            $table->mediumText('content')->nullable();
            $table->boolean('status')->default(1)->index('status');

            //list
            $table->json('images')->nullable();
            $table->json('response')->nullable()->comment('Chứa thông tin mọi người phản hồi về bài đăng (views, reaction, share)');

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
        Schema::dropIfExists('social_posts');
    }
}
