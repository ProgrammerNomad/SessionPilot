<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Roots\Acorn\ServiceProvider;
use ProgrammerNomad\SessionPilot\Http\Controllers\SessionController;
use ProgrammerNomad\SessionPilot\Http\Controllers\LogController;
use ProgrammerNomad\SessionPilot\Http\Controllers\RuleController;
use ProgrammerNomad\SessionPilot\Http\Controllers\DeviceController;

class RestApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        $namespace = 'sp/v1';

        register_rest_route($namespace, '/sessions', [
            'methods'             => 'GET',
            'callback'            => [SessionController::class, 'index'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);

        register_rest_route($namespace, '/sessions/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [SessionController::class, 'destroy'],
            'permission_callback' => [$this, 'requireManageOptions'],
            'args'                => ['id' => ['validate_callback' => fn($v) => is_numeric($v)]],
        ]);

        register_rest_route($namespace, '/sessions/kill', [
            'methods'             => 'POST',
            'callback'            => [SessionController::class, 'kill'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);

        register_rest_route($namespace, '/logs', [
            'methods'             => 'GET',
            'callback'            => [LogController::class, 'index'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);

        register_rest_route($namespace, '/rules', [
            'methods'             => 'GET',
            'callback'            => [RuleController::class, 'index'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);

        register_rest_route($namespace, '/rules', [
            'methods'             => 'POST',
            'callback'            => [RuleController::class, 'store'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);

        register_rest_route($namespace, '/rules/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [RuleController::class, 'destroy'],
            'permission_callback' => [$this, 'requireManageOptions'],
            'args'                => ['id' => ['validate_callback' => fn($v) => is_numeric($v)]],
        ]);

        register_rest_route($namespace, '/devices', [
            'methods'             => 'GET',
            'callback'            => [DeviceController::class, 'index'],
            'permission_callback' => [$this, 'requireManageOptions'],
        ]);
    }

    public function requireManageOptions(): bool
    {
        return current_user_can('manage_options');
    }
}
