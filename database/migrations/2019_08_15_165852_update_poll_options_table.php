<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePollOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('poll_options', function (Blueprint $table) {
            $table->integer('post_id')->nullable();
            $table->integer('maximum')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropColumn('post_id');
            $table->dropColumn('maximum');
        });
    }
}
