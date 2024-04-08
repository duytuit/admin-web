<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBOCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('b_o_customers', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->integer('cb_id');
			$table->integer('status')->default(1)->nullable()->comment('-1, 0, 1');
			$table->integer('user_id')->nullable();
			$table->string('cb_name')->nullable();
			$table->integer('source_id')->nullable()->comment('nguồn khách hàng (thư ký phân bổ/ tự nhập,..)');
			$table->string('cb_source')->nullable();
			$table->string('cb_account')->nullable()->comment('= sđt');
			$table->string('cb_staff_id', 256)->nullable()->comment('nhân viên quản lý');
			$table->string('cb_password')->nullable();
			$table->string('cb_permanent_address')->nullable();
			$table->date('cb_issue_date')->nullable();
			$table->string('cb_issue_place')->nullable();
			$table->string('cb_id_passport')->nullable();
			$table->string('cb_email')->nullable();
			$table->string('cb_phone')->nullable();
			$table->string('cb_avatar')->nullable();
			$table->boolean('cb_login')->nullable();
			$table->integer('project_id')->nullable()->comment('dự án quan tâm');
			$table->integer('tc_created_by')->nullable()->comment('người thêm');
			$table->string('device_token')->nullable()->comment('Token thiết bị để gửi FCM');
			$table->text('phone_variants')->nullable()->comment('Số điện thoại khác');
			$table->date('birthday')->nullable();
			$table->string('cmnd')->nullable();
			$table->date('cmnd_date')->nullable();
			$table->string('issued_by')->nullable();
			$table->string('city')->nullable();
			$table->string('district')->nullable();
			$table->string('address')->nullable();
			$table->integer('partner_id')->nullable();
			$table->json('group_id')->nullable();
			$table->json('files')->nullable();
			$table->softDeletes();
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
		Schema::drop('b_o_customers');
	}

}
