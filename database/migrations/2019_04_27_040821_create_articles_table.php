<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticlesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('articles', function(Blueprint $table) {
            $table->bigIncrements('id', true);

            // user_id
            $table->integer('user_id')->index('user_id')->nullable();

            // type
            $table->enum('type', array('article', 'event', 'voucher'))->index('type');
            $table->integer('category_id')->index('category_id');

            // list
            $table->json('customer_ids')->nullable();
            $table->json('customer_group_ids')->nullable();
            
            // notify
            $table->json('notify')->nullable();

            // url alias
            $table->bigInteger('url_id')->nullable()->index('url_id');
            $table->string('alias')->nullable()->index('alias');

            // common
            $table->string('title');
            $table->text('summary')->nullable();
            $table->mediumText('content')->nullable();
            $table->text('image')->nullable();
            $table->string('hashtag')->nullable();            
            $table->integer('num_views')->default(0)->index('num_views');
            $table->boolean('status')->default(1)->index('status');
            $table->boolean('private')->default(1)->index('private');
            $table->dateTime('publish_at')->nullable()->index('publish_at');

            // json
            $table->json('images')->nullable();
            $table->json('attaches')->nullable();
            $table->json('poll_options')->nullable();

            // voucher
            $table->string('address')->nullable()->comment('Event');;

            // event, voucher
            $table->string('qr_code')->nullable()->comment('Event, Voucher');
            $table->dateTime('start_at')->nullable()->index('start_at')->comment('Event, Voucher');
            $table->dateTime('end_at')->nullable()->index('end_at')->comment('Event, Voucher');

            // voucher
            $table->integer('number')->nullable()->default(0)->comment('Voucher');
            $table->integer('partner_id')->nullable()->index('partner_id')->comment('Voucher');
            $table->string('voucher_code', 128)->nullable()->index('voucher_code')->comment('Voucher');
            $table->enum('voucher_type', array('public', 'request'))->default('public')->index('voucher_type')->comment('Voucher');

            // common
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('articles');
    }

}
