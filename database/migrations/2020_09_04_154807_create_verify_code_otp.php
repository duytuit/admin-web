<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifyCodeOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify_code_otp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pub_users_id')->index('pub_user_id');
            $table->string('otp_code');
            $table->string('mobile', 254)->nullable();
            $table->integer('otp_timeout');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('verify_code_otp');
    }
}
