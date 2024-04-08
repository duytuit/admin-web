<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnBdcHandbookCategoryIdAndTypeIdNullToBdcHandbooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_handbooks', function (Blueprint $table) {
            //
              $table->integer('bdc_handbook_category_id')->index('bdc_handbook_category_id')->nullable()->unsigned()->change();
               $table->integer('bdc_handbook_type_id')->index('bdc_handbook_type_id')->nullable()->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_handbooks', function (Blueprint $table) {
            //
        });
    }
}
