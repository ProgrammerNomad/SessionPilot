<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Console\Commands;

use WP_CLI;
use WP_CLI_Command;

/**
 * Manage SessionPilot activity logs.
 */
class LogsCommand extends WP_CLI_Command
{
    /**
     * List activity logs.
     *
     * ## OPTIONS
     *
     * [--user=<id>]
     * : Filter by user ID.
     *
     * [--since=<date>]
     * : Show logs after this date (e.g. 2026-01-01).
     *
     * [--action=<type>]
     * : Filter by action type (e.g. login, logout, role_change).
     *
     * [--format=<format>]
     * : Output format: table, json, csv. Default: table.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot logs list
     *     wp sessionpilot logs list --user=3 --since=2026-01-01
     *     wp sessionpilot logs list --action=login_failed
     *
     * @subcommand list
     */
    public function list(array $args, array $assoc): void
    {
        global $wpdb;

        $where  = ['1=1'];
        $params = [];

        if ( ! empty($assoc['user']) ) {
            $where[]  = 'user_id = %d';
            $params[] = (int) $assoc['user'];
        }

        if ( ! empty($assoc['since']) ) {
            $where[]  = 'timestamp > %s';
            $params[] = sanitize_text_field($assoc['since']);
        }

        if ( ! empty($assoc['action']) ) {
            $where[]  = 'action_type = %s';
            $params[] = sanitize_text_field($assoc['action']);
        }

        $whereClause = implode(' AND ', $where);

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, user_id, action_type, description, ip, severity, timestamp FROM {$wpdb->prefix}sp_activity_logs WHERE {$whereClause} ORDER BY timestamp DESC LIMIT 100",
                ...$params
            ),
            ARRAY_A
        );

        WP_CLI\Utils\format_items($assoc['format'] ?? 'table', $logs, ['id', 'user_id', 'action_type', 'severity', 'ip', 'timestamp', 'description']);
    }
}
