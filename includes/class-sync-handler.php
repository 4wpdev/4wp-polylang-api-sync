<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Polylang_API_Sync_Handler {

    public static function sync_taxonomy( $data ) {
        // Placeholder logic: return received data
        return [
            'status' => 'success',
            'type' => 'taxonomy',
            'received' => $data
        ];
    }

    public static function sync_posts( $data ) {
        // Placeholder logic: return received data
        return [
            'status' => 'success',
            'type' => 'posts',
            'received' => $data
        ];
    }
}