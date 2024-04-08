<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id', true);
            $table->bigInteger('url_id')->nullable()->index('url_id');
            $table->enum('type', array('article', 'event', 'voucher', 'feedback'))->default('article')->index('type');
            $table->bigInteger('post_id')->index('post_id');
            $table->bigInteger('parent_id')->default(0)->index('parent_id');
            $table->integer('user_id')->index('user_id');
            $table->enum('user_type', array('user', 'customer', 'partner'))->index('user_type');
            $table->string('user_name')->nullable();
            $table->text('content')->nullable();
            $table->json('files')->nullable();
            $table->integer('rating')->nullable()->index('rating');
            $table->boolean('status')->default(0)->index('status');
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
        Schema::drop('comments');
    }

}
