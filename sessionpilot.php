<?php
/**
 * Plugin Name:       SessionPilot
 * Plugin URI:        https://github.com/ProgrammerNomad/SessionPilot
 * Description:       Open-source session & activity monitoring for logged-in WordPress users. Self-hosted, privacy-first, no cloud dependency.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
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

// Activation / deactivation hooks — must be registered before Acorn boots
register_activation_hook( __FILE__, [ \ProgrammerNomad\SessionPilot\Installer::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \ProgrammerNomad\SessionPilot\Installer::class, 'deactivate' ] );

// Boot Acorn application
\Roots\bootloader( __FILE__ );
