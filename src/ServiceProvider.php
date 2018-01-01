<?php

namespace Iankov\Thumbnails;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/' => base_path('config/'),
        ], 'thumbnail_config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/thumbnails.php', 'thumbnails');

        include __DIR__.'/helpers.php';

        if(config('thumbnails.use_package_routes')) {
            include __DIR__ . '/routes.php';
        }
    }
}
