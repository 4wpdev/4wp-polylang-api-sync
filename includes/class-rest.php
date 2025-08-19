<?php
/**
 * REST API Endpoints Class
 *
 * @package 4wp-polylang-api-sync
 */

namespace Forwp\PolylangApiSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Rest {
    
    /**
     * API namespace
     */
    protected $namespace = '4wp-polylang-sync/v1';
    
    /**
     * Sync handler instance
     */
    private $sync_handler;
    
    /**
     * Validator instance
     */
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct( $sync_handler ) {
        $this->sync_handler = $sync_handler;
        $this->validator = new Validator();
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] register_routes called' );
        }
        
        // Taxonomy sync endpoint
        register_rest_route( $this->namespace, '/taxonomy', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'handle_taxonomy_sync' ],
            'permission_callback' => [ $this, 'check_taxonomy_permissions' ],
            'args' => [
                'taxonomy' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_taxonomy' ]
                ],
                'source_term_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => [ $this->validator, 'validate_term_exists' ]
                ],
                'source_lang' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_language' ]
                ],
                'target_term_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => [ $this->validator, 'validate_term_exists' ]
                ],
                'target_lang' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_language' ]
                ]
            ],
        ]);

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Taxonomy endpoint registered: ' . $this->namespace . '/taxonomy' );
        }

        // Posts sync endpoint
        register_rest_route( $this->namespace, '/posts', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'handle_posts_sync' ],
            'permission_callback' => [ $this, 'check_posts_permissions' ],
            'args' => [
                'source_post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => [ $this->validator, 'validate_post_exists' ]
                ],
                'source_lang' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_language' ]
                ],
                'target_post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => [ $this->validator, 'validate_post_exists' ]
                ],
                'target_lang' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_language' ]
                ]
            ],
        ]);

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Posts endpoint registered: ' . $this->namespace . '/posts' );
        }

        // Get available languages endpoint
        register_rest_route( $this->namespace, '/languages', [
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => [ $this, 'get_available_languages' ],
            'permission_callback' => [ $this, 'check_read_permissions' ],
        ]);

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Languages endpoint registered: ' . $this->namespace . '/languages' );
        }

        // Get taxonomy terms endpoint
        register_rest_route( $this->namespace, '/taxonomy/(?P<taxonomy>[a-zA-Z0-9_-]+)/terms', [
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => [ $this, 'get_taxonomy_terms' ],
            'permission_callback' => [ $this, 'check_read_permissions' ],
            'args' => [
                'taxonomy' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_taxonomy' ]
                ],
                'lang' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [ $this->validator, 'validate_language' ]
                ]
            ],
        ]);

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Taxonomy terms endpoint registered: ' . $this->namespace . '/taxonomy/{taxonomy}/terms' );
        }
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] All REST routes registered successfully' );
        }
    }
    
    /**
     * Check taxonomy sync permissions
     */
    public function check_taxonomy_permissions( $request ) {
        // Check if user is authenticated
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_forbidden', 'Authentication required.', [ 'status' => 401 ] );
        }
        
        // Check if user can manage terms
        if ( ! current_user_can( 'manage_terms' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', [ 'status' => 403 ] );
        }
        
        // Check nonce for security
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Invalid nonce.', [ 'status' => 403 ] );
        }
        
        return true;
    }
    
    /**
     * Check posts sync permissions
     */
    public function check_posts_permissions( $request ) {
        // Check if user is authenticated
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_forbidden', 'Authentication required.', [ 'status' => 401 ] );
        }
        
        // Check if user can edit posts
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', [ 'status' => 403 ] );
        }
        
        // Check nonce for security
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new \WP_Error( 'rest_forbidden', 'Invalid nonce.', [ 'status' => 403 ] );
        }
        
        return true;
    }
    
    /**
     * Check read permissions
     */
    public function check_read_permissions( $request ) {
        // Check if user is authenticated
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_forbidden', 'Authentication required.', [ 'status' => 401 ] );
        }
        
        return true;
    }
    
    /**
     * Handle taxonomy synchronization
     */
    public function handle_taxonomy_sync( $request ) {
        try {
            $params = $request->get_params();
            
            // Log the request for audit
            $this->log_request( 'taxonomy_sync', $params );
            
            // Perform the sync
            $result = $this->sync_handler->sync_taxonomy_terms( $params );
            
            if ( is_wp_error( $result ) ) {
                return $result;
            }
            
            return new \WP_REST_Response( [
                'success' => true,
                'message' => 'Taxonomy terms synchronized successfully',
                'data' => $result
            ], 200 );
            
        } catch ( \Exception $e ) {
            return new \WP_Error( 'sync_error', $e->getMessage(), [ 'status' => 500 ] );
        }
    }
    
    /**
     * Handle posts synchronization
     */
    public function handle_posts_sync( $request ) {
        try {
            $params = $request->get_params();
            
            // Log the request for audit
            $this->log_request( 'posts_sync', $params );
            
            // Perform the sync
            $result = $this->sync_handler->sync_posts( $params );
            
            if ( is_wp_error( $result ) ) {
                return $result;
            }
            
            return new \WP_REST_Response( [
                'success' => true,
                'message' => 'Posts synchronized successfully',
                'data' => $result
            ], 200 );
            
        } catch ( \Exception $e ) {
            return new \WP_Error( 'sync_error', $e->getMessage(), [ 'status' => 500 ] );
        }
    }
    
    /**
     * Get available languages
     */
    public function get_available_languages( $request ) {
        try {
            $languages = $this->sync_handler->get_available_languages();
            
            return new \WP_REST_Response( [
                'success' => true,
                'data' => $languages
            ], 200 );
            
        } catch ( \Exception $e ) {
            return new \WP_Error( 'languages_error', $e->getMessage(), [ 'status' => 500 ] );
        }
    }
    
    /**
     * Get taxonomy terms
     */
    public function get_taxonomy_terms( $request ) {
        try {
            $taxonomy = $request->get_param( 'taxonomy' );
            $lang = $request->get_param( 'lang' );
            
            $terms = $this->sync_handler->get_taxonomy_terms( $taxonomy, $lang );
            
            return new \WP_REST_Response( [
                'success' => true,
                'data' => $terms
            ], 200 );
            
        } catch ( \Exception $e ) {
            return new \WP_Error( 'terms_error', $e->getMessage(), [ 'status' => 500 ] );
        }
    }
    
    /**
     * Log request for audit
     */
    private function log_request( $action, $params ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( sprintf(
                '[4WP Polylang Sync] %s: %s by user %d',
                $action,
                json_encode( $params ),
                get_current_user_id()
            ) );
        }
    }
}