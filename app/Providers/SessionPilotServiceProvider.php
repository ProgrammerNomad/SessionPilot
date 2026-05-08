<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Roots\Acorn\ServiceProvider;
use ProgrammerNomad\SessionPilot\Services\SessionService;
use ProgrammerNomad\SessionPilot\Services\ActivityLogService;
use ProgrammerNomad\SessionPilot\Services\DeviceService;

class SessionPilotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SessionService::class);
        $this->app->singleton(ActivityLogService::class);
        $this->app->singleton(DeviceService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(SESSIONPILOT_PLUGIN_DIR . 'database/migrations');
        $this->loadViewsFrom(SESSIONPILOT_PLUGIN_DIR . 'resources/views', 'sessionpilot');

        // Register all sub-providers
        $this->app->register(HooksServiceProvider::class);
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(RestApiServiceProvider::class);
        $this->app->register(CliServiceProvider::class);
        $this->app->register(CronServiceProvider::class);
    }
}
