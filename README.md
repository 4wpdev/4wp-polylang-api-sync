# 4WP Polylang API Sync

Plugin for synchronizing taxonomies and posts between languages in Polylang via REST API.

**Current Version:** 1.2.0  
**Last Updated:** August 19, 2024

## Description

This plugin creates custom REST API endpoints for linking taxonomies and posts between different languages in Polylang. It solves the problem of missing API in the free version of Polylang for content synchronization.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Polylang (Free or Pro)
- User with `manage_terms` capabilities for taxonomies or `edit_posts` for posts

## Installation

1. Download the plugin to `/wp-content/plugins/` directory
2. Activate the plugin through WordPress admin panel
3. Ensure Polylang is activated

## Quick Start

### Basic Taxonomy Synchronization
```php
$response = wp_remote_post(home_url('/wp-json/4wp-polylang-api-sync/v1/taxonomy'), [
    'headers' => [
        'Content-Type' => 'application/json',
        'X-WP-Nonce' => wp_create_nonce('wp_rest')
    ],
    'body' => json_encode([
        'taxonomy' => 'category',
        'source_term_id' => 123,
        'source_lang' => 'uk',
        'target_term_id' => 456,
        'target_lang' => 'en'
    ])
]);
```

### Basic Posts Synchronization
```php
$response = wp_remote_post(home_url('/wp-json/4wp-polylang-api-sync/v1/posts'), [
    'headers' => [
        'Content-Type' => 'application/json',
        'X-WP-Nonce' => wp_create_nonce('wp_rest')
    ],
    'body' => json_encode([
        'source_post_id' => 789,
        'source_lang' => 'uk',
        'target_post_id' => 101,
        'target_lang' => 'en'
    ])
]);
```

## API Endpoints

### Base URL
```
/wp-json/4wp-polylang-api-sync/v1/
```

### 1. Taxonomy Synchronization

**POST** `/taxonomy`

Links taxonomy terms between languages.

**Parameters:**
```json
{
    "taxonomy": "category",
    "source_term_id": 123,
    "source_lang": "uk",
    "target_term_id": 456,
    "target_lang": "en"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Taxonomy terms synchronized successfully",
    "data": {
        "source_term_id": 123,
        "target_term_id": 456,
        "source_lang": "uk",
        "target_lang": "en",
        "taxonomy": "category",
        "sync_date": "2024-08-19 20:18:00"
    }
}
```

### 2. Posts Synchronization

**POST** `/posts`

Links posts between languages.

**Parameters:**
```json
{
    "source_post_id": 789,
    "source_lang": "uk",
    "target_post_id": 101,
    "target_lang": "en"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Posts synchronized successfully",
    "data": {
        "source_post_id": 789,
        "target_post_id": 101,
        "source_lang": "uk",
        "target_lang": "en",
        "sync_date": "2024-08-19 20:18:00"
    }
}
```

### 3. Get Available Languages

**GET** `/languages`

Returns list of all available languages in Polylang.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "slug": "uk",
            "name": "Українська",
            "flag": "🇺🇦"
        },
        {
            "slug": "en",
            "name": "English",
            "flag": "🇬🇧"
        }
    ]
}
```

### 4. Get Taxonomy Terms

**GET** `/taxonomy/{taxonomy}/terms`

Returns taxonomy terms with optional language filtering.

**Parameters:**
- `lang` (optional) - language code for filtering

**Example:**
```
GET /wp-json/4wp-polylang-api-sync/v1/taxonomy/category/terms?lang=uk
```

## Security

- All requests require authentication
- Taxonomy operations require `manage_terms` capabilities
- Post operations require `edit_posts` capabilities
- Nonce verification for CSRF protection

## Hooks and Filters

The plugin provides extensive hooks and filters for developers to extend functionality.

### Action Hooks

#### General Sync Hooks
```php
// Fired before any sync operation
do_action( 'forwp_polylang_api_sync_before_sync', $sync_type, $params );

// Fired after successful sync
do_action( 'forwp_polylang_api_sync_after_sync', $sync_type, $params, $result );

// Fired when sync fails
do_action( 'forwp_polylang_api_sync_sync_failed', $sync_type, $params, $error );
```

#### Taxonomy Specific Hooks
```php
// Before taxonomy sync
do_action( 'forwp_polylang_api_sync_before_taxonomy_sync', $params );

// After taxonomy sync
do_action( 'forwp_polylang_api_sync_after_taxonomy_sync', $params, $result );

// When taxonomy translations are saved
do_action( 'forwp_polylang_api_sync_taxonomy_translations_saved', $params, $translations );
```

#### Posts Specific Hooks
```php
// Before posts sync
do_action( 'forwp_polylang_api_sync_before_posts_sync', $params );

// After posts sync
do_action( 'forwp_polylang_api_sync_after_posts_sync', $params, $result );

// When post translations are saved
do_action( 'forwp_polylang_api_sync_post_translations_saved', $params, $translations );
```

#### Plugin Lifecycle Hooks
```php
// When plugin is loaded
do_action( 'forwp_polylang_api_sync_loaded', $plugin_instance );

// When plugin initializes
do_action( 'forwp_polylang_api_sync_init' );

// When REST API is initialized
do_action( 'forwp_polylang_api_sync_rest_api_initialized', $rest_handler );

// When admin is initialized
do_action( 'forwp_polylang_api_sync_admin_init' );
```

### Filter Hooks

#### Parameter Modification
```php
// Modify taxonomy sync parameters
$params = apply_filters( 'forwp_polylang_api_sync_taxonomy_params', $params );

// Modify posts sync parameters
$params = apply_filters( 'forwp_polylang_api_sync_posts_params', $params );
```

#### Response Data Modification
```php
// Modify taxonomy sync response
$response = apply_filters( 'forwp_polylang_api_sync_taxonomy_response', $response_data, $params );

// Modify posts sync response
$response = apply_filters( 'forwp_polylang_api_sync_posts_response', $response_data, $params );
```

#### Translation Data Modification
```php
// Modify taxonomy translations before saving
$params = apply_filters( 'forwp_polylang_api_sync_before_save_taxonomy_translations', $params );

// Modify final taxonomy translations
$translations = apply_filters( 'forwp_polylang_api_sync_taxonomy_translations', $translations, $params );

// Modify post translations before saving
$params = apply_filters( 'forwp_polylang_api_sync_before_save_post_translations', $params );

// Modify final post translations
$translations = apply_filters( 'forwp_polylang_api_sync_post_translations', $translations, $params );
```

#### Data Retrieval Modification
```php
// Modify available languages
$languages = apply_filters( 'forwp_polylang_api_sync_available_languages', $languages );

// Modify taxonomy terms query arguments
$args = apply_filters( 'forwp_polylang_api_sync_taxonomy_terms_args', $args, $taxonomy, $lang );

// Modify taxonomy terms data
$terms = apply_filters( 'forwp_polylang_api_sync_taxonomy_terms', $terms, $taxonomy, $lang );
```

#### Component Modification
```php
// Modify sync handler
$sync_handler = apply_filters( 'forwp_polylang_api_sync_sync_handler', $sync_handler );

// Modify REST handler
$rest_handler = apply_filters( 'forwp_polylang_api_sync_rest_handler', $rest_handler );
```

#### Permission and Logging
```php
// Modify user permission check
$has_permission = apply_filters( 'forwp_polylang_api_sync_user_permission', $has_permission, $capability, $user_id );

// Modify log data
$log_data = apply_filters( 'forwp_polylang_api_sync_log_data', $log_data, $action, $params );
```

### Example: Custom Hook Usage

```php
// Add custom logging
add_action( 'forwp_polylang_api_sync_after_taxonomy_sync', function( $params, $result ) {
    // Log to external service
    error_log( 'Taxonomy synced: ' . json_encode( $result ) );
}, 10, 2 );

// Modify sync parameters
add_filter( 'forwp_polylang_api_sync_taxonomy_params', function( $params ) {
    // Add custom validation or modification
    $params['custom_field'] = 'custom_value';
    return $params;
});

// Modify response data
add_filter( 'forwp_polylang_api_sync_taxonomy_response', function( $response, $params ) {
    // Add custom data to response
    $response['custom_info'] = 'Additional information';
    return $response;
}, 10, 2 );

// Handle sync failures
add_action( 'forwp_polylang_api_sync_sync_failed', function( $sync_type, $params, $error ) {
    // Send notification or perform cleanup
    wp_mail( 'admin@example.com', 'Sync Failed', $error->get_error_message() );
}, 10, 3 );
```

## Usage Examples

### JavaScript (Fetch API)

```javascript
// Synchronize taxonomy
const syncTaxonomy = async () => {
    const response = await fetch('/wp-json/4wp-polylang-api-sync/v1/taxonomy', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify({
            taxonomy: 'category',
            source_term_id: 123,
            source_lang: 'uk',
            target_term_id: 456,
            target_lang: 'en'
        })
    });
    
    const result = await response.json();
    console.log(result);
};
```

### PHP (WordPress)

```php
// Synchronize taxonomy
$response = wp_remote_post(home_url('/wp-json/4wp-polylang-api-sync/v1/taxonomy'), [
    'headers' => [
        'Content-Type' => 'application/json',
        'X-WP-Nonce' => wp_create_nonce('wp_rest')
    ],
    'body' => json_encode([
        'taxonomy' => 'category',
        'source_term_id' => 123,
        'source_lang' => 'uk',
        'target_term_id' => 456,
        'target_lang' => 'en'
    ])
]);

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    // Process response
}
```

## Logging

When `WP_DEBUG` is enabled, all synchronization actions are logged to WordPress log with prefix `[4WP Polylang Sync]`.

**Log Location:** `/wp-content/debug.log`

**Example Log Entry:**
```
[19-Aug-2024 20:18:00] [4WP Polylang Sync] {"action":"taxonomy_sync","params":{"taxonomy":"category","source_term_id":123,"source_lang":"uk","target_term_id":456,"target_lang":"en"},"success":true,"user_id":1,"timestamp":"2024-08-19 20:18:00","ip":"192.168.1.1"}
```

## Plugin Structure

```
4wp-polylang-api-sync/
├── 4wp-polylang-api-sync.php (main file)
├── includes/
│   ├── class-plugin.php (main class)
│   ├── class-rest.php (REST API endpoints)
│   ├── class-sync-handler.php (synchronization logic)
│   └── class-validator.php (data validation)
├── languages/ (translations)
├── README.md (this file)
├── CHANGELOG.md (version history)
└── LICENSE
```

## Namespace

Plugin uses `Forwp\PolylangApiSync` namespace for all classes.

## Version History

- **v1.1.0** (Current) - Complete rewrite with security, hooks, and documentation
- **v1.0.0** - Initial release with basic structure

For detailed version history, see [CHANGELOG.md](CHANGELOG.md).

## Roadmap

### Next Version (v1.2.0)
- Rate limiting and DoS protection
- Enhanced logging system with admin panel
- Performance optimizations

### Future Versions
- Batch operations and caching
- Advanced analytics and monitoring
- Enterprise features and multi-site support

## License

GPL v2 or later

## Author

4WP Dev - https://4wp.dev

## Support

For support and feature requests, please visit our GitHub repository or contact us at https://4wp.dev
