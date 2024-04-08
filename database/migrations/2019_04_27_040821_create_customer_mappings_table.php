<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomerMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customer_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('id_user');
			$table->integer('id_customer')->comment('0: Tự tạo, 1: Được gán');
			$table->integer('type')->nullable();			
			$table->string('c_title')->nullable()->comment('Tên khách hàng');
			$table->string('c_name')->nullable()->comment('Tên khách hàng trên CMND');
			$table->string('c_phone')->nullable()->comment('SĐT khách hàng');
			$table->text('c_address')->nullable()->comment('ĐC khách hàng');
			$table->text('c_note')->nullable()->comment('Ghi chú khách hàng');
			$table->integer('c_rating')->default(0)->comment('Điểm tiềm năng khách hàng');
			$table->string('id_passport')->nullable()->comment('chứng minh thư nhân dân');
			$table->date('issue_date')->nullable();
			$table->string('issue_place')->nullable();
			$table->string('permanent_address')->nullable();
			$table->string('email')->nullable();
			$table->string('potential_reference_code')->nullable()->comment('ma khach hang tiem nang tavico');
			$table->string('customer_reference_code')->nullable()->comment('ma khach hang chính thức');
			$table->text('c_images')->nullable();
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
		Schema::drop('customer_mappings');
	}

}
