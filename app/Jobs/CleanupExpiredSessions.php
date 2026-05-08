<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Jobs;

class CleanupExpiredSessions
{
    public function handle(): void
    {
        global $wpdb;

        // Mark sessions expired by WP_Session_Tokens as logged out
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}sp_sessions
                 SET logged_out_at = %s
                 WHERE logged_out_at IS NULL
                   AND expires_at IS NOT NULL
                   AND expires_at < %s",
                current_time('mysql'),
                current_time('mysql')
            )
        );

        // Expire sessions that missed heartbeats (idle timeout)
        $gracePeriod = (int) $wpdb->get_var(
            "SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = 'heartbeat_grace_period'"
        ) ?: 120;

        $idleTimeout = (int) $wpdb->get_var(
            "SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = 'idle_timeout_seconds'"
        ) ?: 0;

        if ($idleTimeout > 0) {
            $cutoff = date('Y-m-d H:i:s', time() - $idleTimeout - $gracePeriod);
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}sp_sessions
                     SET logged_out_at = %s
                     WHERE logged_out_at IS NULL
                       AND last_activity < %s",
                    current_time('mysql'),
                    $cutoff
                )
            );
        }
    }
}
