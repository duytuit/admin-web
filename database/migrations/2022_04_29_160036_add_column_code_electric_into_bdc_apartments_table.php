<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCodeElectricIntoBdcApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdc_apartments', function (Blueprint $table) {
            $table->string('code_electric')->nullable()->comment('mã công tơ điện');
            $table->string('code_water')->nullable()->comment('mã công tơ nước');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdc_apartments', function (Blueprint $table) {
            //
        });
    }
}
