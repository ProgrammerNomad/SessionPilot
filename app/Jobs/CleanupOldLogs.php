<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Jobs;

class CleanupOldLogs
{
    public function handle(): void
    {
        global $wpdb;

        $retentionDays = (int) $wpdb->get_var(
            "SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = 'log_retention_days'"
        ) ?: 90;

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}sp_activity_logs WHERE timestamp < %s",
                $cutoff
            )
        );

        $sessionRetentionDays = (int) $wpdb->get_var(
            "SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = 'session_retention_days'"
        ) ?: 30;

        $sessionCutoff = date('Y-m-d H:i:s', strtotime("-{$sessionRetentionDays} days"));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}sp_sessions WHERE logged_out_at IS NOT NULL AND logged_out_at < %s",
                $sessionCutoff
            )
        );
    }
}
