<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPubUsersId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_business_partners', function (Blueprint $table) {
             $table->integer('pub_users_id')->index('pub_users_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_business_partners', function (Blueprint $table) {
             $table->dropColumn('pub_users_id')->index('pub_users_id');
        });
    }
}
