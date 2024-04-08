<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bo_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cb_id')->nullable();
            $table->integer('status')->default(1)->nullable();
            $table->integer('parent_id')->default(0)->nullable();
            $table->string('cb_code')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('cb_title')->nullable();
            $table->string('alias')->nullable();
            $table->text('cb_description')->nullable();
            $table->json('extra_ids')->nullable();
            $table->integer('updated_user_id')->nullable();
            $table->integer('investor_id')->nullable();
            $table->integer('created_user_id')->nullable();
            $table->integer('cb_level')->default(1)->nullable();
            $table->dateTime('last_sync_tvc')->nullable();
            $table->string('type')->default('private')->nullable();
            $table->integer('apartment_grid')->default(1)->nullable();
            $table->integer('active_booking')->default(1)->nullable();
            $table->integer('enable_list_price')->default(0)->nullable();
            $table->integer('send_mail')->default(1)->nullable();
            $table->dateTime('ub_updated_time')->nullable();
            $table->dateTime('ub_created_time')->nullable();
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
        Schema::dropIfExists('bo_categories');
    }
}
