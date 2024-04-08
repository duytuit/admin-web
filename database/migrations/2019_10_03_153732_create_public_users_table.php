<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 254)->nullable()->index('email');
            $table->string('password', 254)->nullable()->index('password');
            $table->tinyInteger('status')->default(1);
            $table->string('remember_token')->nullable()->comment('Token đăng nhập Web');
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
        Schema::dropIfExists('pub_users');
    }
}
