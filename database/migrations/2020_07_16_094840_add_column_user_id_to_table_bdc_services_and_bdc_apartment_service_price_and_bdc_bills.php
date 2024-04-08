<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUserIdToTableBdcServicesAndBdcApartmentServicePriceAndBdcBills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
         Schema::table('bdc_services', function (Blueprint $table) {
            $table->integer('user_id')->index('user_id');
        });
          Schema::table('bdc_apartment_service_price', function (Blueprint $table) {
            $table->integer('user_id')->index('user_id');
        });
           Schema::table('bdc_bills', function (Blueprint $table) {
            $table->integer('user_id')->index('user_id');
        });
          Schema::table('bdc_bills', function (Blueprint $table) {
            $table->integer('approved_id')->index('approved_id')->nullable()->unsigned();
        });
          Schema::table('bdc_bills', function (Blueprint $table) {
            $table->integer('sender_id')->index('sender_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
         Schema::table('bdc_services', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
          Schema::table('bdc_apartment_service_price', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
          Schema::table('bdc_bills', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
          Schema::table('bdc_bills', function (Blueprint $table) {
            $table->dropColumn('approved_id')->index('approved_id')->nullable()->unsigned();
        });
          Schema::table('bdc_bills', function (Blueprint $table) {
            $table->dropColumn('sender_id')->index('sender_id')->nullable()->unsigned();
        });
    }
}
