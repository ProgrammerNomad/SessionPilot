<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Roots\Acorn\ServiceProvider;
use ProgrammerNomad\SessionPilot\Jobs\CleanupExpiredSessions;
use ProgrammerNomad\SessionPilot\Jobs\CleanupOldLogs;

class CronServiceProvider extends ServiceProvider
{
    private const CLEANUP_HOOK = 'sessionpilot_cleanup';

    public function boot(): void
    {
        add_action(self::CLEANUP_HOOK, [$this, 'runCleanup']);
        add_filter('cron_schedules', [$this, 'addSchedules']);

        if ( ! wp_next_scheduled(self::CLEANUP_HOOK) ) {
            wp_schedule_event(time(), 'hourly', self::CLEANUP_HOOK);
        }
    }

    public function runCleanup(): void
    {
        $this->app->make(CleanupExpiredSessions::class)->handle();
        $this->app->make(CleanupOldLogs::class)->handle();
    }

    public function addSchedules(array $schedules): array
    {
        return $schedules;
    }

    public static function clearSchedule(): void
    {
        $timestamp = wp_next_scheduled(self::CLEANUP_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CLEANUP_HOOK);
        }
    }
}
