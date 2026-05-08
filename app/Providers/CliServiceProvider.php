<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Roots\Acorn\ServiceProvider;

class CliServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ( ! defined('WP_CLI') || ! WP_CLI ) {
            return;
        }

        \WP_CLI::add_command(
            'sessionpilot sessions',
            \ProgrammerNomad\SessionPilot\Console\Commands\SessionsCommand::class
        );

        \WP_CLI::add_command(
            'sessionpilot logs',
            \ProgrammerNomad\SessionPilot\Console\Commands\LogsCommand::class
        );

        \WP_CLI::add_command(
            'sessionpilot rules',
            \ProgrammerNomad\SessionPilot\Console\Commands\RulesCommand::class
        );

        \WP_CLI::add_command(
            'sessionpilot devices',
            \ProgrammerNomad\SessionPilot\Console\Commands\DevicesCommand::class
        );
    }
}
