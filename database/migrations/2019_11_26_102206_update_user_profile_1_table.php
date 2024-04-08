<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserProfile1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
      Schema::table('pub_user_profile', function (Blueprint $table) {
            $table->integer('type_profile')->default(0);
            $table->text('config_fcm')->nullable();
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
        Schema::table('pub_user_profile', function (Blueprint $table) {
            $table->dropColumn('type_profile');
            $table->dropColumn('config_fcm');
        });
    }
}
