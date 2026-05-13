<?php
/**
 * Plugin Name:       SessionPilot
 * Plugin URI:        https://github.com/ProgrammerNomad/SessionPilot
 * Description:       Open-source session & activity monitoring for logged-in WordPress users. Self-hosted, privacy-first, no cloud dependency.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Shiv Singh
 * Author URI:        https://github.com/ProgrammerNomad
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sessionpilot
 * Domain Path:       /resources/lang
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SESSIONPILOT_VERSION', '1.0.0' );
define( 'SESSIONPILOT_PLUGIN_FILE', __FILE__ );
define( 'SESSIONPILOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SESSIONPILOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SESSIONPILOT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader (Composer)
if ( file_exists( SESSIONPILOT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once SESSIONPILOT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>SessionPilot:</strong> Composer dependencies are missing. Run <code>composer install</code> in the plugin directory.</p></div>';
    } );
    return;
}

// Activation / deactivation hooks -must be registered before Acorn boots
register_activation_hook( __FILE__, [ \ProgrammerNomad\SessionPilot\Installer::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \ProgrammerNomad\SessionPilot\Installer::class, 'deactivate' ] );

// Tell Acorn where the plugin root is
if ( ! defined( 'ACORN_BASEPATH' ) ) {
    define( 'ACORN_BASEPATH', SESSIONPILOT_PLUGIN_DIR );
}

// Plugins list page: action links (left column — next to Deactivate)
add_filter( 'plugin_action_links_' . SESSIONPILOT_PLUGIN_BASENAME, function ( array $links ): array {
    $action_links = [
        'settings' => sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=sessionpilot-settings' ) ),
            esc_html__( 'Settings', 'sessionpilot' )
        ),
        'dashboard' => sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=sessionpilot' ) ),
            esc_html__( 'Dashboard', 'sessionpilot' )
        ),
    ];

    return array_merge( $action_links, $links );
} );

// Plugins list page: row meta links (right column — next to Version)
add_filter( 'plugin_row_meta', function ( array $meta, string $file ): array {
    if ( SESSIONPILOT_PLUGIN_BASENAME !== $file ) {
        return $meta;
    }

    $meta[] = sprintf(
        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        esc_url( 'https://github.com/ProgrammerNomad/SessionPilot' ),
        esc_html__( 'GitHub', 'sessionpilot' )
    );

    $meta[] = sprintf(
        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        esc_url( 'https://github.com/ProgrammerNomad/SessionPilot/issues' ),
        esc_html__( 'Report a Bug', 'sessionpilot' )
    );

    $meta[] = sprintf(
        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        esc_url( 'https://github.com/ProgrammerNomad/SessionPilot/blob/main/docs/README.md' ),
        esc_html__( 'Documentation', 'sessionpilot' )
    );

    return $meta;
}, 10, 2 );

// Boot Acorn on after_setup_theme (standard Acorn boot hook)
// Explicitly register our ServiceProvider via callback so it works even when
// Acorn's PackageManifest cannot auto-discover it (e.g. composer.json absent).
add_action( 'after_setup_theme', function () {
    \Roots\bootloader()->boot( function ( $app ) {
        $app->register( \ProgrammerNomad\SessionPilot\Providers\SessionPilotServiceProvider::class );
    } );
}, 1 );
