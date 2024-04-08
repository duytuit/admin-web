<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBdcReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->integer('config_id')->nullable();
            $table->string('type')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->dropColumn('config_id');
            $table->dropColumn('type');
            $table->dropColumn('status');
        });
    }
}
