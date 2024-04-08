<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBoUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bo_users', function(Blueprint $table)
        {
            $table->bigIncrements('id', true);

            $table->integer('ub_id')->unique('ub_id');
            $table->string('group_ids')->nullable();
            $table->integer('level_id')->nullable();

            $table->string('ub_account_name')->nullable()->unique('ub_account_name');
            $table->string('ub_title')->nullable()->index('ub_title');
            $table->string('ub_email')->nullable()->index('ub_email');
            $table->string('ub_phone')->nullable()->index('ub_phone');
            $table->string('password')->nullable()->index('password');
            $table->string('ub_token')->nullable()->comment('FCM Token');

            $table->string('ub_staff_code')->nullable()->comment('Mã nhân viên DXMB');
            $table->string('ub_tvc_code')->nullable()->comment('Mã nhân viên Tavico');
            $table->string('ub_avatar')->nullable();
            $table->string('ub_account_tvc')->nullable();
            $table->string('ub_portrait_image')->nullable()->comment('ảnh hồ sơ');

            $table->text('ub_info_images')->nullable()->comment('ảnh thông tin khác');

            $table->string('security_code')->nullable();
            $table->string('remember_token')->nullable()->comment('Token đăng nhập Web');
            $table->text('remember_jwt')->nullable()->comment('JWT đăng nhập từ app');
            $table->string('signature')->nullable()->comment('chu ky nhan vien');

            $table->boolean('is_verified')->nullable()->default(0)->comment('Tình trạng xác thực email');

            $table->integer('ub_status')->nullable()->default(0);

            $table->dateTime('ub_last_logged_time')->nullable();
            $table->dateTime('ub_created_time')->nullable();

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
        Schema::drop('bo_users');
    }

}
