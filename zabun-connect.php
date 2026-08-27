<?php
/**
 * Plugin Name:       Zabun Connect
 * Plugin URI:        https://digitalfeedingsconceptsite.com
 * Description:       Integrates Zabun CRM API to WordPress with cached database storage, shortcodes, and Elementor widgets.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Suliman Khan
 * Author URI:        https://sulimankhan.pro
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       zabun-connect
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// Plugin Constants
define( 'ZABUN_CONNECT_VERSION', '1.0.0' );
define( 'ZABUN_CONNECT_DB_VERSION', '1.0.0' );
define( 'ZABUN_CONNECT_FILE', __FILE__ );
define( 'ZABUN_CONNECT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZABUN_CONNECT_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader implementation.
 * Attempts to load composer autoload if present, otherwise provides PSR-4 fallback.
 */
if ( file_exists( ZABUN_CONNECT_PATH . 'vendor/autoload.php' ) ) {
    require_once ZABUN_CONNECT_PATH . 'vendor/autoload.php';
} else {
    spl_autoload_register( function ( $class ) {
        $prefix   = 'ZabunConnect\\';
        $base_dir = ZABUN_CONNECT_PATH . 'src/';

        $len = strlen( $prefix );
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }

        $relative_class = substr( $class, $len );
        $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    } );
}

/**
 * Plugin activation hook.
 */
register_activation_hook( __FILE__, function () {
    \ZabunConnect\Database\Schema::create_tables();
    if ( class_exists( '\ZabunConnect\Sync\Scheduler' ) ) {
        \ZabunConnect\Sync\Scheduler::instance()->schedule_event();
    }
} );

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
    if ( class_exists( '\ZabunConnect\Sync\Scheduler' ) ) {
        \ZabunConnect\Sync\Scheduler::clear_events();
    }
} );

/**
 * Bootstrap plugin on plugins_loaded.
 */
add_action( 'plugins_loaded', function () {
    \ZabunConnect\Plugin::instance()->init();
} );
