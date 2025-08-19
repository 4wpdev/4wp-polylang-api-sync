<?php
/**
 * Plugin Name: 4WP Polylang API Sync
 * Description: Adds a custom REST API endpoint for synchronizing taxonomy and post translations in Polylang.
 * Tags: polylang, api, sync, translations, 4wp
 * Version: 1.0.0
 * Author: 4wp.dev
 * Author URI: https://4wp.dev
 * Text Domain: 4wp-polylang-api-sync
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'WP_POLYLANG_API_SYNC_VERSION', '1.0.0' );
define( 'WP_POLYLANG_API_SYNC_DIR', plugin_dir_path( __FILE__ ) );

require_once WP_POLYLANG_API_SYNC_DIR . 'includes/class-rest-endpoints.php';
require_once WP_POLYLANG_API_SYNC_DIR . 'includes/class-sync-handler.php';

add_action( 'rest_api_init', function () {
    $rest = new WP_Polylang_API_Sync_REST();
    $rest->register_routes();
} );