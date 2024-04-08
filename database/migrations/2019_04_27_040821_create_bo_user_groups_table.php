<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBoUserGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bo_user_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('gb_id')->unique('b_o_user_groups_gb_id_unique');
			$table->integer('gb_status')->default(0)->comment('Trạng thái nhóm');
			$table->string('gb_title', 256)->nullable()->comment('Tên Nhóm');
			$table->string('gb_code')->nullable();
			$table->text('gb_description', 65535)->nullable();
			$table->string('reference_code', 256)->nullable()->comment('Sàn trên CRM - tavico');
			$table->integer('level_id')->nullable();
			$table->dateTime('gb_created_time')->nullable();
			$table->dateTime('gb_updated_time')->nullable();
			$table->integer('gb_updated_user')->nullable();
			$table->integer('gb_created_user')->nullable();
			$table->integer('user_leader_id')->nullable()->comment('Giám đốc sàn');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bo_user_groups');
	}

}
