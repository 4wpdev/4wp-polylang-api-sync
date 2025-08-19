<?php
/**
 * Main Plugin Class
 *
 * @package 4wp-polylang-api-sync
 */

namespace Forwp\PolylangApiSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Plugin {

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * REST API handler
     */
    private $rest_handler;

    /**
     * Sync handler
     */
    private $sync_handler;

    /**
     * Constructor
     */
    public function __construct() {
        // Check if all required classes are available
        if ( ! $this->check_required_classes() ) {
            add_action( 'admin_notices', array( $this, 'missing_classes_notice' ) );
            return;
        }
        
        $this->init_hooks();
        $this->init_components();

        // Fire plugin loaded hook
        do_action( 'forwp_polylang_api_sync_loaded', $this );
    }

    /**
     * Check if all required classes are available
     */
    private function check_required_classes() {
        $required_classes = array(
            'Forwp\\PolylangApiSync\\SyncHandler',
            'Forwp\\PolylangApiSync\\Rest',
            'Forwp\\PolylangApiSync\\Validator'
        );
        
        foreach ( $required_classes as $class ) {
            if ( ! class_exists( $class ) ) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Admin notice for missing classes
     */
    public function missing_classes_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __( '4WP Polylang API Sync: Some required classes could not be loaded. Please deactivate and reactivate the plugin.', '4wp-ppl-api' );
        echo '</p></div>';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );

        // Add custom hooks for extensibility
        add_action( 'forwp_polylang_api_sync_before_sync', array( $this, 'before_sync_hook' ), 10, 2 );
        add_action( 'forwp_polylang_api_sync_after_sync', array( $this, 'after_sync_hook' ), 10, 3 );
        add_action( 'forwp_polylang_api_sync_sync_failed', array( $this, 'sync_failed_hook' ), 10, 3 );

        // Add filters for data modification
        add_filter( 'forwp_polylang_api_sync_sync_params', array( $this, 'filter_sync_params' ), 10, 2 );
        add_filter( 'forwp_polylang_api_sync_response_data', array( $this, 'filter_response_data' ), 10, 3 );
        add_filter( 'forwp_polylang_api_sync_permission_check', array( $this, 'filter_permission_check' ), 10, 3 );
    }

    /**
     * Initialize components
     */
    private function init_components() {
        $this->sync_handler = new SyncHandler();
        $this->rest_handler = new Rest( $this->sync_handler );

        // Allow other plugins to modify components
        $this->sync_handler = apply_filters( 'forwp_polylang_api_sync_sync_handler', $this->sync_handler );
        $this->rest_handler = apply_filters( 'forwp_polylang_api_sync_rest_handler', $this->rest_handler );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Text domain already loaded in main file

        // Fire init hook
        do_action( 'forwp_polylang_api_sync_init' );
    }

    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] init_rest_api called' );
        }
        
        if ( $this->rest_handler ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Registering REST routes...' );
            }
            
            $this->rest_handler->register_routes();

            // Fire REST API initialized hook
            do_action( 'forwp_polylang_api_sync_rest_api_initialized', $this->rest_handler );
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] REST routes registered successfully' );
            }
        } else {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] REST handler is null!' );
            }
        }
    }

    /**
     * Admin initialization
     */
    public function admin_init() {
        // Add admin notices if needed
        if ( ! function_exists( 'pll_current_language' ) ) {
            add_action( 'admin_notices', array( $this, 'polylang_missing_notice' ) );
        }

        // Fire admin init hook
        do_action( 'forwp_polylang_api_sync_admin_init' );
    }

    /**
     * Admin notice for missing Polylang
     */
    public function polylang_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __( '4WP Polylang API Sync requires Polylang plugin to be installed and activated.', '4wp-ppl-api' );
        echo '</p></div>';
    }

    /**
     * Before sync hook
     */
    public function before_sync_hook( $sync_type, $params ) {
        // This hook allows other plugins to perform actions before sync
        do_action( "forwp_polylang_api_sync_before_{$sync_type}", $params );
    }

    /**
     * After sync hook
     */
    public function after_sync_hook( $sync_type, $params, $result ) {
        // This hook allows other plugins to perform actions after successful sync
        do_action( "forwp_polylang_api_sync_after_{$sync_type}", $params, $result );
    }

    /**
     * Sync failed hook
     */
    public function sync_failed_hook( $sync_type, $params, $error ) {
        // This hook allows other plugins to handle sync failures
        do_action( "forwp_polylang_api_sync_failed_{$sync_type}", $params, $error );
    }

    /**
     * Filter sync parameters
     */
    public function filter_sync_params( $params, $sync_type ) {
        // This filter allows other plugins to modify sync parameters
        return apply_filters( "forwp_polylang_api_sync_{$sync_type}_params", $params );
    }

    /**
     * Filter response data
     */
    public function filter_response_data( $data, $sync_type, $params ) {
        // This filter allows other plugins to modify response data
        return apply_filters( "forwp_polylang_api_sync_{$sync_type}_response", $data, $params );
    }

    /**
     * Filter permission check
     */
    public function filter_permission_check( $has_permission, $capability, $user_id ) {
        // This filter allows other plugins to modify permission checks
        return apply_filters( 'forwp_polylang_api_sync_user_permission', $has_permission, $capability, $user_id );
    }

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get sync handler instance
     */
    public function get_sync_handler() {
        return $this->sync_handler;
    }

    /**
     * Get REST handler instance
     */
    public function get_rest_handler() {
        return $this->rest_handler;
    }
}
