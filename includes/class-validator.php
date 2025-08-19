<?php
/**
 * Data Validator Class
 *
 * @package 4wp-polylang-api-sync
 */

namespace Forwp\PolylangApiSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Validator {
    
    /**
     * Validate taxonomy name
     */
    public function validate_taxonomy( $taxonomy ) {
        if ( empty( $taxonomy ) ) {
            return false;
        }
        
        // Check if taxonomy exists
        if ( ! taxonomy_exists( $taxonomy ) ) {
            return false;
        }
        
        // Check if taxonomy is translatable in Polylang
        if ( ! $this->is_taxonomy_translatable( $taxonomy ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate term exists
     */
    public function validate_term_exists( $term_id ) {
        if ( empty( $term_id ) || ! is_numeric( $term_id ) ) {
            return false;
        }
        
        $term = get_term( $term_id );
        return ! is_wp_error( $term ) && $term !== null;
    }
    
    /**
     * Validate post exists
     */
    public function validate_post_exists( $post_id ) {
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            return false;
        }
        
        $post = get_post( $post_id );
        return ! is_wp_error( $post ) && $post !== null;
    }
    
    /**
     * Validate language
     */
    public function validate_language( $lang ) {
        if ( empty( $lang ) ) {
            return false;
        }
        
        // Check if language exists in Polylang
        $languages = $this->get_polylang_languages();
        return in_array( $lang, $languages, true );
    }
    
    /**
     * Check if taxonomy is translatable in Polylang
     */
    private function is_taxonomy_translatable( $taxonomy ) {
        if ( ! function_exists( 'pll_get_post_translations' ) ) {
            return false;
        }
        
        // Get Polylang options
        $options = get_option( 'polylang' );
        if ( ! $options || ! isset( $options['taxonomies'] ) ) {
            return false;
        }
        
        return in_array( $taxonomy, $options['taxonomies'], true );
    }
    
    /**
     * Get available Polylang languages
     */
    private function get_polylang_languages() {
        if ( ! function_exists( 'pll_languages_list' ) ) {
            return [];
        }
        
        return pll_languages_list( 'slug' );
    }
    
    /**
     * Validate sync parameters
     */
    public function validate_sync_params( $params ) {
        $errors = [];
        
        // Check required fields
        $required_fields = [ 'source_term_id', 'source_lang', 'target_term_id', 'target_lang' ];
        foreach ( $required_fields as $field ) {
            if ( empty( $params[ $field ] ) ) {
                $errors[] = sprintf( 'Field "%s" is required.', $field );
            }
        }
        
        // Check if source and target are different
        if ( $params['source_lang'] === $params['target_lang'] ) {
            $errors[] = 'Source and target languages must be different.';
        }
        
        // Check if source and target terms are different
        if ( $params['source_term_id'] === $params['target_term_id'] ) {
            $errors[] = 'Source and target terms must be different.';
        }
        
        return empty( $errors ) ? true : $errors;
    }
    
    /**
     * Sanitize sync parameters
     */
    public function sanitize_sync_params( $params ) {
        return [
            'taxonomy' => sanitize_text_field( $params['taxonomy'] ?? '' ),
            'source_term_id' => absint( $params['source_term_id'] ?? 0 ),
            'source_lang' => sanitize_text_field( $params['source_lang'] ?? '' ),
            'target_term_id' => absint( $params['target_term_id'] ?? 0 ),
            'target_lang' => sanitize_text_field( $params['target_lang'] ?? '' ),
        ];
    }
}
