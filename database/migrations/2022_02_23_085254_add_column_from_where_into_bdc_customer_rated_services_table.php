<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFromWhereIntoBdcCustomerRatedServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_customer_rated_services', function (Blueprint $table) {
            $table->string('from_where')->nullable()->comment('Người tạo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_customer_rated_services', function (Blueprint $table) {
            //
        });
    }
}
