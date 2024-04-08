<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldCustomerCodeToPubUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pub_user_profile', function (Blueprint $table) {
            $table->string('customer_code_prefix',4)->nullable();
            $table->integer('customer_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pub_user_profile', function (Blueprint $table) {
            $table->dropColumn('customer_code_prefix');
            $table->dropColumn('customer_code');
        });
    }
}
