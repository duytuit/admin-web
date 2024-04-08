<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePubGroupPermistionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("ALTER TABLE `pub_group_permission` MODIFY COLUMN `permission_ids` json NULL AFTER `description`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement("ALTER TABLE `pub_group_permission` MODIFY COLUMN `permission_ids` json NOT NULL AFTER `description`");
    }
}
