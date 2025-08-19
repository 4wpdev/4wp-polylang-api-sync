<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Polylang_API_Sync_REST {

    protected $namespace = '4wp-polylang-sync/v1';

    public function register_routes() {
        register_rest_route( $this->namespace, '/taxonomy', [
            'methods'  => 'POST',
            'callback' => [ $this, 'handle_taxonomy' ],
            'permission_callback' => function() {
                return current_user_can( 'manage_terms' );
            },
            'args' => [
                'taxonomy' => ['required' => true],
                'translations' => ['required' => true],
            ],
        ]);

        register_rest_route( $this->namespace, '/posts', [
            'methods'  => 'POST',
            'callback' => [ $this, 'handle_posts' ],
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
            'args' => [
                'post_id' => ['required' => true],
                'translations' => ['required' => true],
            ],
        ]);
    }

    public function handle_taxonomy( $request ) {
        $data = $request->get_json_params();
        return WP_Polylang_API_Sync_Handler::sync_taxonomy( $data );
    }

    public function handle_posts( $request ) {
        $data = $request->get_json_params();
        return WP_Polylang_API_Sync_Handler::sync_posts( $data );
    }
}