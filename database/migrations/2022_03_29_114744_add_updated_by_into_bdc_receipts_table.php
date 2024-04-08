<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedByIntoBdcReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            $table->integer('updated_by')->nullable()->comment('Người cập nhật lần cuối');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_receipts', function (Blueprint $table) {
            //
        });
    }
}
