<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table)
        {
            $table->increments('id');
            $table->enum('type', array('article', 'event', 'voucher'))->index('type');
            $table->integer('user_id')->index('user_id');

            // url alias
            $table->bigInteger('url_id')->nullable()->index('url_id');
            $table->string('alias')->nullable()->index('alias');
            
            // content
            $table->string('title')->nullable();
            $table->text('content', 65535)->nullable();
            $table->string('icon_web')->nullable();
            $table->string('icon_app')->nullable();

            $table->boolean('status')->default(1)->index('status');

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
        Schema::dropIfExists('categories');
    }

}
