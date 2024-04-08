<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicGroupsUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_groups_user', function (Blueprint $table) {

            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_vietnamese_ci';
            $table->bigIncrements('id');
            $table->string('name', 254)->nullable();
            $table->text('description', 1000)->nullable();
            $table->integer('admin_id')->index('admin_id')->comment('id user pub duoc chon lam admin cua group');
            $table->integer('parent_id')->index('parent_id')->comment('id group parent dung hien thi group theo cay thu muc');
            $table->text('pub_user_ids')->nullable()->comment('danh sach id user thuoc nhom duoc luu duoi dang serialize([1,2,3,4])');
            $table->text('permission_ids')->nullable()->comment('danh sach id quyen cua nhom duoc luu duoi dang  serialize([1,2,3,4])');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('pub_groups_user');
    }
}
