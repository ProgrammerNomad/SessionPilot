<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot;

class Installer
{
    public static function activate(): void
    {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // 1. Sessions
        dbDelta( "CREATE TABLE {$wpdb->prefix}sp_sessions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(255) NOT NULL,
            device_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            browser VARCHAR(100) NULL,
            browser_version VARCHAR(50) NULL,
            os VARCHAR(100) NULL,
            device_type VARCHAR(20) NULL,
            created_at DATETIME NOT NULL,
            last_activity DATETIME NULL,
            expires_at DATETIME NULL,
            logged_out_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY user_id (user_id),
            KEY device_id (device_id),
            KEY ip_address (ip_address),
            KEY created_at (created_at)
        ) $charset;" );

        // 2. Activity logs
        dbDelta( "CREATE TABLE {$wpdb->prefix}sp_activity_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            action_type VARCHAR(100) NOT NULL,
            description TEXT NULL,
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            severity ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
            timestamp DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY severity (severity),
            KEY timestamp (timestamp)
        ) $charset;" );

        // 3. Rules
        dbDelta( "CREATE TABLE {$wpdb->prefix}sp_rules (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_role VARCHAR(100) NULL,
            user_id BIGINT UNSIGNED NULL,
            max_sessions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            enforcement_mode ENUM('block_new','logout_oldest','logout_all') NOT NULL DEFAULT 'logout_oldest',
            idle_timeout_seconds INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_role_user_id (user_role, user_id),
            KEY user_role (user_role),
            KEY user_id (user_id)
        ) $charset;" );

        // 4. Devices
        dbDelta( "CREATE TABLE {$wpdb->prefix}sp_devices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            device_name VARCHAR(100) NULL,
            browser VARCHAR(100) NULL,
            browser_version VARCHAR(50) NULL,
            os VARCHAR(100) NULL,
            device_type VARCHAR(20) NULL,
            last_ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL,
            last_seen DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;" );

        // 5. Settings
        dbDelta( "CREATE TABLE {$wpdb->prefix}sp_settings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL,
            setting_value LONGTEXT NULL,
            autoload VARCHAR(3) NOT NULL DEFAULT 'yes',
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset;" );

        // Insert default settings (INSERT IGNORE so re-activation is safe)
        $now      = current_time( 'mysql' );
        $defaults = [
            [ 'log_retention_days',      '90'   ],
            [ 'session_retention_days',  '30'   ],
            [ 'idle_timeout_seconds',    '1800' ],
            [ 'heartbeat_interval',      '30'   ],
            [ 'heartbeat_grace_period',  '120'  ],
            [ 'anonymize_ip',            '0'    ],
            [ 'alert_email',             ''     ],
            [ 'alert_on_limit_exceeded', '1'    ],
            [ 'alert_on_login_failures', '1'    ],
            [ 'login_failure_threshold', '5'    ],
        ];

        foreach ( $defaults as [ $key, $value ] ) {
            $wpdb->query( $wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->prefix}sp_settings
                    (setting_key, setting_value, autoload, created_at, updated_at)
                 VALUES (%s, %s, 'yes', %s, %s)",
                $key, $value, $now, $now
            ) );
        }

        update_option( 'sessionpilot_version', SESSIONPILOT_VERSION );
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook( 'sessionpilot_cleanup' );
        flush_rewrite_rules();
    }
}
