<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomerDiariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customer_diaries', function(Blueprint $table)
		{
			$table->bigIncrements('id', true);
			$table->integer('cd_id');
			$table->integer('campaign_id')->nullable();
			$table->integer('cd_user_id')->nullable();
			$table->integer('cd_customer_id');
			$table->text('cd_description')->nullable();
			$table->dateTime('cd_time')->nullable();
			$table->integer('cd_rating')->default(5);
			$table->boolean('status')->default(0);
			$table->integer('cd_category')->nullable();
			$table->integer('tmp_id')->nullable();
			$table->integer('project_id');
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
		Schema::drop('customer_diaries');
	}

}
