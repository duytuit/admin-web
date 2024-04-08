<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFeedback3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `feedback` CHANGE COLUMN `customer_id` `pub_user_profile_id` bigint(20) NOT NULL AFTER `id`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `feedback` CHANGE COLUMN `customer_id` `pub_user_profile_id` bigint(20) NOT NULL AFTER `id`');
    }
}
