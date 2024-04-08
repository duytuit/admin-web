<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPartnersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_partners', function (Blueprint $table) {
            $table->bigIncrements('id', true);
            $table->bigInteger('user_id');
            $table->integer('status')->nullable()->default(0);
            $table->string('group_ids')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable()->unique('ub_email');
            $table->string('phone')->nullable()->index('phone');
            $table->string('password')->nullable()->index('password');
            $table->string('ub_token')->nullable()->comment('FCM Token');
            $table->dateTime('ub_last_logged_time')->nullable();
            $table->string('avatar')->nullable();
            $table->string('security_code')->nullable();
            $table->string('remember_token')->nullable()->comment('Token đăng nhập Web');
            $table->string('remember_jwt')->nullable()->comment('JWT đăng nhập từ app');
            $table->boolean('is_verified')->nullable()->default(0)->comment('Tình trạng xác thực email');
            $table->integer('partner_id')->nullable()->index('partner_id');
            $table->integer('branch_id')->nullable()->index('branch_id');
            $table->string('user_name')->nullable()->index('user_name');
            $table->string('partner_name')->nullable();
            $table->string('branch_name')->nullable();
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
        Schema::dropIfExists('user_partners');
    }

}
