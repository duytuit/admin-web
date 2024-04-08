<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsSupperAdminInPubUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pub_users', function (Blueprint $table) {
            //
            $table->tinyInteger('isadmin')->default(0)->comment('0: user, 1: SupperAdmin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pub_users', function (Blueprint $table) {
            //
             $table->dropColumn('isadmin');
        });
    }
}
