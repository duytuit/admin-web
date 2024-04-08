<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNbRecordsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nb_records_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title')->comment('Tên kiểu');
        });
        //insert data to table
        DB::table('nb_records_type')->insert([
                ['title' => 'Thu Tiền'],
                ['title' => 'Chi Tiền']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nb_records_type');
    }
}
