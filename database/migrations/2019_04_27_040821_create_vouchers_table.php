<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVouchersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id', true);
            $table->bigInteger('article_id')->index('article_id');
            $table->bigInteger('user_id')->index('user_id');
            $table->enum('user_type', array('user', 'customer', 'partner'))->index('user_type');
            $table->dateTime('check_in')->nullable()->index('check_in');
            $table->dateTime('used_at')->nullable()->index('used_at');
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
        Schema::drop('vouchers');
    }

}
