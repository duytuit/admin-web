<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAparmentSuggestionsIntoHistoryTransactionAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('history_transaction_accounting', function (Blueprint $table) {
            $table->longText('aparment_suggestions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('history_transaction_accounting', function (Blueprint $table) {
            //
        });
    }
}
