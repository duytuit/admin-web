<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCampaignAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_assigns', function (Blueprint $table) {
            $table->dropColumn('staff_id');
            $table->dropColumn('staff_name');
            $table->string('staff_account')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_assigns', function (Blueprint $table) {
            $table->bigInteger('staff_id')->index('staff_id');
            $table->string('staff_name')->nullable();
            $table->dropColumn('staff_account');
        });
    }
}
