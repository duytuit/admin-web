<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_assigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index('user_id');
            $table->unsignedBigInteger('campaign_id')->index('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->bigInteger('staff_id')->index('staff_id');
            $table->string('staff_name')->nullable();
            $table->integer('status')->default(1);
			$table->string('customer_name')->nullable();
			$table->string('customer_phone')->nullable();
			$table->string('customer_email')->nullable();
			$table->string('description')->nullable();
			$table->integer('start')->nullable();
			$table->boolean('feedback')->nullable();
			$table->string('source')->nullable();
			$table->integer('check_diary')->default(0);
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
        Schema::dropIfExists('campaign_assigns');
    }
}
