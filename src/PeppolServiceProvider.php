<?php

namespace Structurize\Peppol;

use Illuminate\Support\ServiceProvider;
use Structurize\Peppol\Services\PeppolService;
use Structurize\Peppol\Console\SyncPeppolCompany;
use Structurize\Peppol\Services\StructurizeService;

class PeppolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/peppol.php', 'peppol');
        $structurizeService = new StructurizeService();
        $this->app->singleton(PeppolService::class, fn($app) =>
        new PeppolService($structurizeService)
        );

        $this->app->alias(PeppolService::class, 'peppol.service');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/peppol.php' => config_path('peppol.php'),
        ], 'peppol-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'peppol-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([SyncPeppolCompany::class]);
        }


    }
}
