<?php

namespace JeremyNikolic\Revision;

use Illuminate\Support\ServiceProvider;
use JeremyNikolic\Revision\Models\Revision;

class RevisionServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                                 __DIR__.'/../config/config.php' => config_path('revisions.php'),
                             ],
                             'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'revisions');
    }

    public static function revisionModel()
    {
        return Revision::class;
    }
}
