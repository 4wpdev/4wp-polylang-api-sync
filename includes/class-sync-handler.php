<?php
/**
 * Sync Handler Class
 *
 * @package 4wp-polylang-api-sync
 */

namespace Forwp\PolylangApiSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SyncHandler {
    
    /**
     * Validator instance
     */
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->validator = new Validator();
    }
    
    /**
     * Sync taxonomy terms between languages
     */
    public function sync_taxonomy_terms( $params ) {
        try {
            // Fire before sync hook
            do_action( 'forwp_polylang_api_sync_before_sync', 'taxonomy', $params );
            
            // Allow other plugins to modify parameters
            $params = apply_filters( 'forwp_polylang_api_sync_taxonomy_params', $params );
            
            // Validate parameters
            $validation = $this->validator->validate_sync_params( $params );
            if ( $validation !== true ) {
                $error = new \WP_Error( 'validation_error', 'Invalid parameters: ' . implode( ', ', $validation ) );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $error );
                return $error;
            }
            
            // Sanitize parameters
            $params = $this->validator->sanitize_sync_params( $params );
            
            // Allow other plugins to perform pre-sync actions
            do_action( 'forwp_polylang_api_sync_before_taxonomy_sync', $params );
            
            // Check if terms exist and are valid
            $source_term = get_term( $params['source_term_id'] );
            $target_term = get_term( $params['target_term_id'] );
            
            if ( is_wp_error( $source_term ) || is_wp_error( $target_term ) ) {
                $error = new \WP_Error( 'term_error', 'One or both terms do not exist.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $error );
                return $error;
            }
            
            // Check if terms belong to the same taxonomy
            if ( $source_term->taxonomy !== $target_term->taxonomy ) {
                $error = new \WP_Error( 'taxonomy_mismatch', 'Terms must belong to the same taxonomy.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $error );
                return $error;
            }
            
            // Check if terms are already linked
            if ( $this->are_terms_linked( $params['source_term_id'], $params['target_term_id'] ) ) {
                $error = new \WP_Error( 'already_linked', 'Terms are already linked.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $error );
                return $error;
            }
            
            // Perform the sync using Polylang functions
            $result = $this->link_taxonomy_terms( $params );
            
            if ( is_wp_error( $result ) ) {
                do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $result );
                return $result;
            }
            
            // Prepare response data
            $response_data = [
                'source_term_id' => $params['source_term_id'],
                'target_term_id' => $params['target_term_id'],
                'source_lang' => $params['source_lang'],
                'target_lang' => $params['target_lang'],
                'taxonomy' => $source_term->taxonomy,
                'sync_date' => current_time( 'mysql' )
            ];
            
            // Allow other plugins to modify response data
            $response_data = apply_filters( 'forwp_polylang_api_sync_taxonomy_response', $response_data, $params );
            
            // Log successful sync
            $this->log_sync_action( 'taxonomy_sync', $params, true );
            
            // Fire after sync hook
            do_action( 'forwp_polylang_api_sync_after_sync', 'taxonomy', $params, $response_data );
            do_action( 'forwp_polylang_api_sync_after_taxonomy_sync', $params, $response_data );
            
            return $response_data;
            
        } catch ( \Exception $e ) {
            $error = new \WP_Error( 'sync_error', 'Failed to sync taxonomy terms: ' . $e->getMessage() );
            $this->log_sync_action( 'taxonomy_sync', $params, false, $e->getMessage() );
            do_action( 'forwp_polylang_api_sync_sync_failed', 'taxonomy', $params, $error );
            return $error;
        }
    }
    
    /**
     * Sync posts between languages
     */
    public function sync_posts( $params ) {
        try {
            // Fire before sync hook
            do_action( 'forwp_polylang_api_sync_before_sync', 'posts', $params );
            
            // Allow other plugins to modify parameters
            $params = apply_filters( 'forwp_polylang_api_sync_posts_params', $params );
            
            // Validate basic parameters
            if ( empty( $params['source_post_id'] ) || empty( $params['target_post_id'] ) ||
                 empty( $params['source_lang'] ) || empty( $params['target_lang'] ) ) {
                $error = new \WP_Error( 'validation_error', 'All parameters are required.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'posts', $params, $error );
                return $error;
            }
            
            // Allow other plugins to perform pre-sync actions
            do_action( 'forwp_polylang_api_sync_before_posts_sync', $params );
            
            // Check if posts exist
            $source_post = get_post( $params['source_post_id'] );
            $target_post = get_post( $params['target_post_id'] );
            
            if ( ! $source_post || ! $target_post ) {
                $error = new \WP_Error( 'post_error', 'One or both posts do not exist.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'posts', $params, $error );
                return $error;
            }
            
            // Check if posts are already linked
            if ( $this->are_posts_linked( $params['source_post_id'], $params['target_post_id'] ) ) {
                $error = new \WP_Error( 'already_linked', 'Posts are already linked.' );
                do_action( 'forwp_polylang_api_sync_sync_failed', 'posts', $params, $error );
                return $error;
            }
            
            // Perform the sync using Polylang functions
            $result = $this->link_posts( $params );
            
            if ( is_wp_error( $result ) ) {
                do_action( 'forwp_polylang_api_sync_sync_failed', 'posts', $params, $result );
                return $result;
            }
            
            // Prepare response data
            $response_data = [
                'source_post_id' => $params['source_post_id'],
                'target_post_id' => $params['target_post_id'],
                'source_lang' => $params['source_lang'],
                'target_lang' => $params['target_lang'],
                'sync_date' => current_time( 'mysql' )
            ];
            
            // Allow other plugins to modify response data
            $response_data = apply_filters( 'forwp_polylang_api_sync_posts_response', $response_data, $params );
            
            // Log successful sync
            $this->log_sync_action( 'posts_sync', $params, true );
            
            // Fire after sync hook
            do_action( 'forwp_polylang_api_sync_after_sync', 'posts', $params, $response_data );
            do_action( 'forwp_polylang_api_sync_after_posts_sync', $params, $response_data );
            
            return $response_data;
            
        } catch ( \Exception $e ) {
            $error = new \WP_Error( 'sync_error', 'Failed to sync posts: ' . $e->getMessage() );
            $this->log_sync_action( 'posts_sync', $params, false, $e->getMessage() );
            do_action( 'forwp_polylang_api_sync_sync_failed', 'posts', $params, $error );
            return $error;
        }
    }
    
    /**
     * Link taxonomy terms using Polylang's logic
     */
    private function link_taxonomy_terms( $params ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Using Polylang\'s logic for linking terms' );
        }
        
        try {
            // First, set the language for both terms
            if ( function_exists( 'pll_set_term_language' ) ) {
                pll_set_term_language( $params['source_term_id'], $params['source_lang'] );
                pll_set_term_language( $params['target_term_id'], $params['target_lang'] );
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Set languages for terms' );
                }
            }
            
            // Create translations array
            $translations = [
                $params['source_lang'] => $params['source_term_id'],
                $params['target_lang'] => $params['target_term_id']
            ];
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Translations array: ' . json_encode( $translations ) );
            }
            
            // Use Polylang's logic: create a linking term with uniqid
            $group = uniqid( 'pll_' );
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Created linking term: ' . $group );
            }
            
            // Insert the linking term into the term_translations taxonomy
            $result = wp_insert_term( $group, 'term_translations', array( 
                'description' => maybe_serialize( $translations ) 
            ));
            
            if ( is_wp_error( $result ) ) {
                return new \WP_Error( 'polylang_error', 'Failed to create linking term: ' . $result->get_error_message() );
            }
            
            $group_term_id = $result['term_id'];
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Linking term created with ID: ' . $group_term_id );
            }
            
            // Link all translations to the new term
            foreach ( $translations as $term_id ) {
                wp_set_object_terms( $term_id, $group_term_id, 'term_translations' );
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Linked term ' . $term_id . ' to group ' . $group_term_id );
                }
            }
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Successfully linked all terms' );
            }
            
            // Fire translation saved hook
            do_action( 'forwp_polylang_api_sync_taxonomy_translations_saved', $params, $translations );
            
            return true;
            
        } catch ( \Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Exception in link_taxonomy_terms: ' . $e->getMessage() );
            }
            return new \WP_Error( 'polylang_error', 'Exception: ' . $e->getMessage() );
        }
    }
    
    /**
     * Link posts using Polylang's logic
     */
    private function link_posts( $params ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[4WP Polylang Sync] Using Polylang\'s logic for linking posts' );
        }
        
        try {
            // First, set the language for both posts
            if ( function_exists( 'pll_set_post_language' ) ) {
                pll_set_post_language( $params['source_post_id'], $params['source_lang'] );
                pll_set_post_language( $params['target_post_id'], $params['target_lang'] );
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Set languages for posts' );
                }
            }
            
            // Create translations array
            $translations = [
                $params['source_lang'] => $params['source_post_id'],
                $params['target_lang'] => $params['target_post_id']
            ];
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Post translations array: ' . json_encode( $translations ) );
            }
            
            // Use Polylang's logic: create a linking term with uniqid
            $group = uniqid( 'pll_' );
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Created linking term for posts: ' . $group );
            }
            
            // Insert the linking term into the post_translations taxonomy
            $result = wp_insert_term( $group, 'post_translations', array( 
                'description' => maybe_serialize( $translations ) 
            ));
            
            if ( is_wp_error( $result ) ) {
                return new \WP_Error( 'polylang_error', 'Failed to create linking term for posts: ' . $result->get_error_message() );
            }
            
            $group_term_id = $result['term_id'];
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Linking term for posts created with ID: ' . $group_term_id );
            }
            
            // Link all translations to the new term
            foreach ( $translations as $post_id ) {
                wp_set_object_terms( $post_id, $group_term_id, 'post_translations' );
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[4WP Polylang Sync] Linked post ' . $post_id . ' to group ' . $group_term_id );
                }
            }
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Successfully linked all posts' );
            }
            
            // Fire translation saved hook
            do_action( 'forwp_polylang_api_sync_post_translations_saved', $params, $translations );
            
            return true;
            
        } catch ( \Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[4WP Polylang Sync] Exception in link_posts: ' . $e->getMessage() );
            }
            return new \WP_Error( 'polylang_error', 'Exception: ' . $e->getMessage() );
        }
    }
    
    /**
     * Check if terms are already linked
     */
    private function are_terms_linked( $term1_id, $term2_id ) {
        $term1_translations = $this->get_term_translations( $term1_id );
        $term2_translations = $this->get_term_translations( $term2_id );
        
        return ! empty( array_intersect( $term1_translations, $term2_translations ) );
    }
    
    /**
     * Check if posts are already linked
     */
    private function are_posts_linked( $post1_id, $post2_id ) {
        $post1_translations = $this->get_post_translations( $post1_id );
        $post2_translations = $this->get_post_translations( $post2_id );
        
        return ! empty( array_intersect( $post1_translations, $post2_translations ) );
    }
    
    /**
     * Get term translations
     */
    private function get_term_translations( $term_id ) {
        if ( ! function_exists( 'pll_get_term_translations' ) ) {
            return [];
        }
        
        $translations = pll_get_term_translations( $term_id );
        return is_array( $translations ) ? $translations : [];
    }
    
    /**
     * Get post translations
     */
    private function get_post_translations( $post_id ) {
        if ( ! function_exists( 'pll_get_post_translations' ) ) {
            return [];
        }
        
        $translations = pll_get_post_translations( $post_id );
        return is_array( $translations ) ? $translations : [];
    }
    
    /**
     * Get available languages
     */
    public function get_available_languages() {
        if ( ! function_exists( 'pll_languages_list' ) ) {
            return [];
        }
        
        $languages = pll_languages_list( 'name' );
        $slugs = pll_languages_list( 'slug' );
        
        $result = [];
        foreach ( $slugs as $index => $slug ) {
            $result[] = [
                'slug' => $slug,
                'name' => $languages[ $index ] ?? $slug,
                'flag' => $this->get_language_flag( $slug )
            ];
        }
        
        // Allow other plugins to modify languages list
        $result = apply_filters( 'forwp_polylang_api_sync_available_languages', $result );
        
        return $result;
    }
    
    /**
     * Get taxonomy terms
     */
    public function get_taxonomy_terms( $taxonomy, $lang = null ) {
        $args = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'all'
        ];
        
        if ( $lang && function_exists( 'pll_get_terms' ) ) {
            $args['lang'] = $lang;
        }
        
        // Allow other plugins to modify query arguments
        $args = apply_filters( 'forwp_polylang_api_sync_taxonomy_terms_args', $args, $taxonomy, $lang );
        
        $terms = get_terms( $args );
        
        if ( is_wp_error( $terms ) ) {
            return [];
        }
        
        $result = [];
        foreach ( $terms as $term ) {
            $term_data = [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $term->count,
                'language' => $this->get_term_language( $term->term_id )
            ];
            
            if ( function_exists( 'pll_get_term_translations' ) ) {
                $term_data['translations'] = pll_get_term_translations( $term->term_id );
            }
            
            $result[] = $term_data;
        }
        
        // Allow other plugins to modify terms data
        $result = apply_filters( 'forwp_polylang_api_sync_taxonomy_terms', $result, $taxonomy, $lang );
        
        return $result;
    }
    
    /**
     * Get language flag
     */
    private function get_language_flag( $lang ) {
        if ( ! function_exists( 'pll_languages_list' ) ) {
            return '';
        }
        
        $flags = pll_languages_list( 'flag' );
        $slugs = pll_languages_list( 'slug' );
        
        $index = array_search( $lang, $slugs );
        return $index !== false ? $flags[ $index ] : '';
    }
    
    /**
     * Get term language
     */
    private function get_term_language( $term_id ) {
        if ( ! function_exists( 'pll_get_term_language' ) ) {
            return '';
        }
        
        return pll_get_term_language( $term_id );
    }
    
    /**
     * Log sync actions
     */
    private function log_sync_action( $action, $params, $success, $error_message = '' ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $log_data = [
                'action' => $action,
                'params' => $params,
                'success' => $success,
                'user_id' => get_current_user_id(),
                'timestamp' => current_time( 'mysql' ),
                'ip' => $this->get_client_ip()
            ];
            
            if ( ! $success && $error_message ) {
                $log_data['error'] = $error_message;
            }
            
            // Allow other plugins to modify log data
            $log_data = apply_filters( 'forwp_polylang_api_sync_log_data', $log_data, $action, $params );
            
            error_log( '[4WP Polylang Sync] ' . json_encode( $log_data ) );
        }
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}