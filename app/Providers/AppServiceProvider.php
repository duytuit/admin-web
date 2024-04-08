<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::enableQueryLog();
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
