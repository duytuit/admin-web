<?php

namespace App\Services\FCM\Provider;

use Illuminate\Support\Str;
use LaravelFCM\Sender\FCMGroup;
use App\Services\FCM\Sender\FCMSender;
use Illuminate\Support\ServiceProvider;
use LaravelFCM\FCMManager;

class FCMServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if (Str::contains($this->app->version(), 'Laravel')) {
            $this->app->configure('fcm');
        } else {
            $this->publishes([
                app_path() . "/../config/fcm.php" => config_path('fcm.php'),
                // __DIR__.'/../config/fcm.php' => config_path('fcm.php'),
            ]);
        }
    }

    public function register()
    {
        // dd(app_path() . "/config/fcm.php");
        if (!Str::contains($this->app->version(), 'Laravel')) {
            $this->mergeConfigFrom(app_path() . "/../config/fcm.php", 'fcm');
            // $this->mergeConfigFrom(__DIR__.'/../config/fcm.php', 'fcm');
        }

        $this->app->singleton('fcm.client', function ($app) {
            return (new FCMManager($app))->driver();
        });

        $this->app->bind('fcm.group', function ($app) {
            $client = $app[ 'fcm.client' ];
            $url = $app[ 'config' ]->get('fcm.http.server_group_url');

            return new FCMGroup($client, $url);
        });

        $this->app->bind('fcm.sender', function ($app) {
            $client = $app[ 'fcm.client' ];
            $url = $app[ 'config' ]->get('fcm.http.server_send_url');

            return new FCMSender($client, $url);
        });
    }

    public function provides()
    {
        return ['fcm.client', 'fcm.group', 'fcm.sender'];
    }
}
