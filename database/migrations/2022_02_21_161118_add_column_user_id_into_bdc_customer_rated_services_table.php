<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUserIdIntoBdcCustomerRatedServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_customer_rated_services', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->comment('người đánh giá');
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
