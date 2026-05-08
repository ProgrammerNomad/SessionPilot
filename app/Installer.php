<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot;

use ProgrammerNomad\SessionPilot\Providers\CronServiceProvider;

class Installer
{
    public static function activate(): void
    {
        // Run migrations on activation
        if ( class_exists('\Roots\Acorn\Application') ) {
            do_action('acorn/init');
        }

        // Flush rewrite rules for REST API
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        CronServiceProvider::clearSchedule();
        flush_rewrite_rules();
    }
}
