<?php

namespace Innoboxrr\Forum;

use Illuminate\Support\ServiceProvider;

class ForumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/Lang', 'forum');
        $this->publishes([
            __DIR__.'/../public/assets' => public_path('vendor/innoboxrr/forum/assets'),
        ], 'forum_assets');

        $this->publishes([
            __DIR__.'/../config/forum.php' => config_path('forum.php'),
        ], 'forum_config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'forum_migrations');

        $this->publishes([
            __DIR__.'/../database/seeds/' => database_path('seeds'),
        ], 'forum_seeds');

        $this->publishes([
            __DIR__.'/Lang' => resource_path('lang/vendor/forum'),
        ], 'forum_lang');

        // include the routes file
        include __DIR__.'/Routes/web.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Register the service provider for the dependency.
         */
        $this->app->register(\LukeTowers\Purifier\PurifierServiceProvider::class);

        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Purifier', 'LukeTowers\Purifier\Facades\Purifier');

        $this->loadViewsFrom(__DIR__.'/Views', 'forum');
    }
}
