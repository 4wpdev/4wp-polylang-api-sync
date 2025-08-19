<?php
/**
 * Plugin Name: 4WP Polylang API Sync
 * Description: Adds a custom REST API endpoint for synchronizing taxonomy and post translations in Polylang.
 * Tags: polylang, api, sync, translations, 4wp
 * Version: 1.2.0
 * Author: 4wp.dev
 * Author URI: https://4wp.dev
 * Text Domain: 4wp-ppl-api
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

namespace Forwp\PolylangApiSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define constants first
define( 'WP_POLYLANG_API_SYNC_VERSION', '1.2.0' );
define( 'WP_POLYLANG_API_SYNC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_POLYLANG_API_SYNC_URL', plugin_dir_url( __FILE__ ) );

// Debug logging
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( '[4WP Polylang Sync] Plugin file loaded' );
}

// Autoloader for classes
spl_autoload_register( function( $class ) {
    $prefix = 'Forwp\\PolylangApiSync\\';
    $base_dir = WP_POLYLANG_API_SYNC_DIR . 'includes/';
    
    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }
    
    $relative_class = substr( $class, $len );
    $file = $base_dir . 'class-' . str_replace( '_', '-', strtolower( $relative_class ) ) . '.php';
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Trying to load: ' . $file . ' for class: ' . $class );
    }
    
    if ( file_exists( $file ) ) {
        require_once $file;
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Successfully loaded: ' . $file );
        }
    } else {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] File not found: ' . $file );
        }
    }
} );

// Manual class loading as fallback
function load_polylang_sync_classes() {
    $base_dir = WP_POLYLANG_API_SYNC_DIR . 'includes/';
    
    $classes = [
        'class-validator.php',
        'class-sync-handler.php', 
        'class-rest.php',
        'class-plugin.php'
    ];
    
    foreach ( $classes as $class_file ) {
        $file_path = $base_dir . $class_file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Manually loaded: ' . $file_path );
            }
        } else {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Manual load failed: ' . $file_path );
            }
        }
    }
}

// Check if Polylang is active - moved to plugins_loaded with higher priority
add_action( 'plugins_loaded', function() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] plugins_loaded action triggered' );
    }
    
    // Load classes manually first
    load_polylang_sync_classes();
    
    // Check if Polylang functions exist
    if ( ! function_exists( 'pll_current_language' ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Polylang function not found' );
        }
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __( '4WP Polylang API Sync requires Polylang plugin to be installed and activated.', '4wp-ppl-api' );
            echo '</p></div>';
        });
        return;
    }
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Polylang function found' );
    }
    
    // Additional check for Polylang class
    if ( ! class_exists( 'Polylang' ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Polylang class not found' );
        }
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __( '4WP Polylang API Sync requires Polylang plugin to be fully loaded.', '4wp-ppl-api' );
            echo '</p></div>';
        });
        return;
    }
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Polylang class found' );
    }
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Polylang checks passed' );
    }
    
    // Load plugin textdomain for translations
    load_plugin_textdomain(
        '4wp-ppl-api',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Textdomain loaded' );
    }
    
    // Check if our classes exist
    if ( ! class_exists( 'Forwp\\PolylangApiSync\\Plugin' ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Plugin class not found after manual loading' );
        }
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __( '4WP Polylang API Sync: Plugin class could not be loaded.', '4wp-ppl-api' );
            echo '</p></div>';
        });
        return;
    }
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Plugin class found, initializing...' );
    }
    
    // Initialize plugin only after Polylang is loaded
    try {
        new Plugin();
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Plugin initialized successfully' );
        }
    } catch ( \Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Error initializing plugin: ' . $e->getMessage() );
        }
        add_action( 'admin_notices', function() use ( $e ) {
            echo '<div class="notice notice-error"><p>';
            echo __( '4WP Polylang API Sync: Error initializing plugin: ', '4wp-ppl-api' ) . $e->getMessage();
            echo '</p></div>';
        });
    }
}, 20 ); // Higher priority than Polylang (which uses priority 1)

// Add a simple test endpoint for debugging
add_action( 'rest_api_init', function() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] rest_api_init action triggered' );
    }
    
    // Simple test endpoint
    register_rest_route( '4wp-polylang-sync/v1', '/test', [
        'methods' => 'GET',
        'callback' => function() {
            return new \WP_REST_Response( [
                'success' => true,
                'message' => '4WP Polylang API Sync is working!',
                'timestamp' => current_time( 'mysql' )
            ], 200 );
        },
        'permission_callback' => '__return_true'
    ] );
    
    // Simple taxonomy endpoint for testing
    register_rest_route( '4wp-polylang-sync/v1', '/taxonomy', [
        'methods' => 'POST',
        'callback' => function( $request ) {
            $params = $request->get_params();
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Taxonomy endpoint called with params: ' . json_encode( $params ) );
            }
            
            // Check if SyncHandler class exists
            if ( ! class_exists( 'Forwp\\PolylangApiSync\\SyncHandler' ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] SyncHandler class not found' );
                }
                return new \WP_REST_Response( [
                    'success' => false,
                    'message' => 'SyncHandler class not found',
                    'error' => 'Plugin not fully initialized'
                ], 500 );
            }
            
            try {
                // Create SyncHandler instance
                $sync_handler = new \Forwp\PolylangApiSync\SyncHandler();
                
                // Perform the sync
                $result = $sync_handler->sync_taxonomy_terms( $params );
                
                if ( is_wp_error( $result ) ) {
                    return new \WP_REST_Response( [
                        'success' => false,
                        'message' => 'Sync failed',
                        'error' => $result->get_error_message()
                    ], 400 );
                }
                
                return new \WP_REST_Response( [
                    'success' => true,
                    'message' => 'Taxonomy terms synchronized successfully',
                    'data' => $result,
                    'timestamp' => current_time( 'mysql' )
                ], 200 );
                
            } catch ( \Exception $e ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Error in taxonomy endpoint: ' . $e->getMessage() );
                }
                
                return new \WP_REST_Response( [
                    'success' => false,
                    'message' => 'Sync failed with exception',
                    'error' => $e->getMessage()
                ], 500 );
            }
        },
        'permission_callback' => '__return_true'
    ] );
    
    // Simple posts endpoint for testing
    register_rest_route( '4wp-polylang-sync/v1', '/posts', [
        'methods' => 'POST',
        'callback' => function( $request ) {
            $params = $request->get_params();
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Posts endpoint called with params: ' . json_encode( $params ) );
            }
            
            // Check if SyncHandler class exists
            if ( ! class_exists( 'Forwp\\PolylangApiSync\\SyncHandler' ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] SyncHandler class not found for posts' );
                }
                return new \WP_REST_Response( [
                    'success' => false,
                    'message' => 'SyncHandler class not found',
                    'error' => 'Plugin not fully initialized'
                ], 500 );
            }
            
            try {
                // Create SyncHandler instance
                $sync_handler = new \Forwp\PolylangApiSync\SyncHandler();
                
                // Perform the sync
                $result = $sync_handler->sync_posts( $params );
                
                if ( is_wp_error( $result ) ) {
                    return new \WP_REST_Response( [
                        'success' => false,
                        'message' => 'Sync failed',
                        'error' => $result->get_error_message()
                    ], 400 );
                }
                
                return new \WP_REST_Response( [
                    'success' => true,
                    'message' => 'Posts synchronized successfully',
                    'data' => $result,
                    'timestamp' => current_time( 'mysql' )
                ], 200 );
                
            } catch ( \Exception $e ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Error in posts endpoint: ' . $e->getMessage() );
                }
                
                return new \WP_REST_Response( [
                    'success' => false,
                    'message' => 'Sync failed with exception',
                    'error' => $e->getMessage()
                ], 500 );
            }
        },
        'permission_callback' => '__return_true'
    ] );
    
    // Simple test endpoint for basic functionality
    register_rest_route( '4wp-polylang-sync/v1', '/test-basic', [
        'methods' => 'GET',
        'callback' => function() {
            // Test basic Polylang functions
            $result = [
                'success' => true,
                'message' => 'Basic functionality test',
                'polylang_functions' => [
                    'pll_current_language' => function_exists( 'pll_current_language' ),
                    'pll_languages_list' => function_exists( 'pll_languages_list' ),
                    'pll_save_term_translations' => function_exists( 'pll_save_term_translations' ),
                    'pll_get_term_translations' => function_exists( 'pll_get_term_translations' )
                ],
                'current_language' => function_exists( 'pll_current_language' ) ? pll_current_language() : 'unknown',
                'available_languages' => function_exists( 'pll_languages_list' ) ? pll_languages_list( 'slug' ) : [],
                'timestamp' => current_time( 'mysql' )
            ];
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Basic test endpoint called: ' . json_encode( $result ) );
            }
            
            return new \WP_REST_Response( $result, 200 );
        },
        'permission_callback' => '__return_true'
    ] );
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[4WP Polylang Sync] Test endpoints registered successfully' );
    }
} );

// Activation hook
register_activation_hook( __FILE__, function() {
    if ( ! function_exists( 'pll_current_language' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'This plugin requires Polylang to be installed and activated.', '4wp-ppl-api' ) );
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
} );

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
} );