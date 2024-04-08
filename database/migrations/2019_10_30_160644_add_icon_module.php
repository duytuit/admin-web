<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIconModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pub_module', function (Blueprint $table) {
            $table->string('icon_web')->default('fa-address-book');
        });
        Schema::table('pub_permissions', function (Blueprint $table) {
            $table->string('icon_web')->default('fa-address-book');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pub_module', function (Blueprint $table) {
            //
        });
    }
}
