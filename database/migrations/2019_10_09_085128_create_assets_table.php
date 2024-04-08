<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdc_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('quantity')->default(1)->nullable()->comment('so luong');
            $table->string('bdc_building_id', 45)->nullable();
            $table->integer('bdc_assets_type_id')->comment('Loai bao tri');
            $table->integer('bdc_period_id')->comment('Loai bao hanh bao tri');
            $table->date('buying_date')->comment('ngay mua');
            $table->string('price', 45)->nullable()->comment('Giá mua');
            $table->integer('using_peroid')->comment('han sư dung');
            $table->date('maintainance_date')->comment('ngay bao tri');
            $table->string('place');
            $table->string('buyer');
            $table->text('asset_note')->nullable();
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
        Schema::dropIfExists('assets');
    }
}
