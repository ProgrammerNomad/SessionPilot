<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Illuminate\Support\ServiceProvider;
use ProgrammerNomad\SessionPilot\Services\SessionService;
use ProgrammerNomad\SessionPilot\Services\ActivityLogService;

class HooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var SessionService $sessions */
        $sessions = $this->app->make(SessionService::class);
        /** @var ActivityLogService $logs */
        $logs = $this->app->make(ActivityLogService::class);

        // Session tracking
        add_action('wp_login', [$sessions, 'onLogin'], 10, 2);
        add_action('wp_logout', [$sessions, 'onLogout'], 10, 1);
        add_action('clear_auth_cookie', [$sessions, 'onAuthCookieCleared']);

        // Heartbeat -keep session last_activity fresh
        add_filter('heartbeat_received', [$sessions, 'onHeartbeat'], 10, 2);

        // Activity logging
        add_action('wp_login', [$logs, 'logLogin'], 10, 2);
        add_action('wp_login_failed', [$logs, 'logLoginFailed'], 10, 2);
        add_action('wp_logout', [$logs, 'logLogout'], 10, 1);
        add_action('set_user_role', [$logs, 'logRoleChange'], 10, 3);
        add_action('activated_plugin', [$logs, 'logPluginActivated'], 10, 1);
        add_action('deactivated_plugin', [$logs, 'logPluginDeactivated'], 10, 1);
        add_action('switch_theme', [$logs, 'logThemeSwitch'], 10, 1);
        add_action('update_option', [$logs, 'logSettingChange'], 10, 3);
        add_action('password_reset', [$logs, 'logPasswordReset'], 10, 1);
    }
}

