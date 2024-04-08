<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBoUserGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bo_user_groups', function (Blueprint $table) {
            $table->integer('app_id')->nullable()->comment('1: DXC, 2:BĐC, ...');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bo_user_groups', function (Blueprint $table) {
            $table->dropColumn('app_id');
        });
    }
}
