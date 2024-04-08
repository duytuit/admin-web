<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_user_profile', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_vietnamese_ci';
            $table->bigIncrements('id');
            $table->integer('pub_user_id')->index('pub_user_id');
            $table->string('display_name', 254)->nullable()->index('display_name');
            $table->string('phone', 45)->nullable()->index('phone');
            $table->string('email', 254)->nullable()->index('email');
            $table->string('address', 254)->nullable()->index('address');
            $table->tinyInteger('type')->nullable();
            $table->string('cmt', 18)->nullable()->index('cmt');
            $table->date('cmt_nc')->nullable();
            $table->string('avatar', 254)->nullable();
            // $table->text('files')->nullable()->comment('Lưu id file của user sở hữu serialize([1,2,3,4])');
            $table->integer('bdc_building_id')->comment('Id Tòa nhà')->default(0);
            $table->date('birthday')->nullable();
            $table->tinyInteger('gender')->default(3)->comment('1:nam, 2: nu, 3: chua xac dinh');
            $table->tinyInteger('status')->default(0)->comment('0: InActive, 1: active');
            $table->string('app_id')->nullable()->index('app_id')->comment('xac dinh nguon ve cua user. Hien thi profile cho app tuong ung');
            $table->string('staff_code', 11)->nullable()->index('staff_code');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pub_user_profile');
    }
}
