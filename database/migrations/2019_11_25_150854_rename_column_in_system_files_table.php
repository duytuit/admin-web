<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnInSystemFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_files', function (Blueprint $table) {
            $table->renameColumn('more_type', 'model_type');
            $table->renameColumn('more_id', 'model_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_files', function (Blueprint $table) {
            $table->renameColumn('model_type', 'more_type');
            $table->renameColumn('model_id', 'more_id');
        });
    }
}
