<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdcCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bdc_apartment_id')->default(0)->comment("Id apartment");
            $table->integer('pub_user_profile_id')->default(0)->comment("Id user profile");
            $table->integer('type')->default(0)->comment("loại cư dân");
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
        Schema::dropIfExists('bdc_customers');
    }
}
