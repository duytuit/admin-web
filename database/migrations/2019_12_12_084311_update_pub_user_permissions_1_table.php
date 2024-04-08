<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePubUserPermissions1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pub_user_permissions', function (Blueprint $table) {
            $table->longText('group_permission_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('pub_user_permissions', function (Blueprint $table) {
            $table->dropColumn('group_permission_ids');
        });
    }
}
