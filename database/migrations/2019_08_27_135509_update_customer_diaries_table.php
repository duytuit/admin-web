<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerDiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_diaries', function (Blueprint $table) {
            $table->json('filters')->nullable();
            $table->integer('campaign_assign_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_diaries', function (Blueprint $table) {
            $table->dropColumn('filters');
            $table->dropColumn('campaign_assign_id');
        });
    }
}
