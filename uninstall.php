<?php
/**
 * SessionPilot Uninstall
 *
 * Runs when a user clicks "Delete" on the plugin in wp-admin.
 * Drops all plugin tables and removes all plugin options.
 *
 * NOT called on deactivate - only on permanent delete.
 */

// WordPress calls this file directly. Bail if not a legitimate uninstall call.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop all plugin tables in reverse dependency order
$tables = [
    $wpdb->prefix . 'sp_activity_logs',
    $wpdb->prefix . 'sp_sessions',
    $wpdb->prefix . 'sp_rules',
    $wpdb->prefix . 'sp_devices',
    $wpdb->prefix . 'sp_settings',
];

foreach ( $tables as $table ) {
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
}

// Remove plugin options
delete_option( 'sessionpilot_version' );

// Clear any scheduled cron events
wp_clear_scheduled_hook( 'sessionpilot_cleanup' );
