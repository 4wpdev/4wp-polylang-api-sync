=== 4WP Polylang API Sync ===
Contributors: 4wp.dev
Tags: polylang, api, sync, translations, 4wp, multilingual, taxonomy, posts
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a custom REST API endpoint for synchronizing taxonomy and post translations in Polylang with comprehensive security and extensibility features.

== Description ==

This plugin creates custom REST API endpoints for linking taxonomies and posts between different languages in Polylang. It solves the problem of missing API in the free version of Polylang for content synchronization.

**Key Features:**
* REST API endpoints for taxonomy and post synchronization
* Comprehensive security with authentication, authorization, and CSRF protection
* Extensive hooks and filters for extensibility
* WordPress debug.log integration for audit trails
* Proper error handling and validation
* PSR-4 compatible autoloader
* Professional code architecture with proper namespacing

**Perfect for:**
* Developers building multilingual applications
* Agencies managing multilingual WordPress sites
* Custom integrations requiring Polylang synchronization
* Extending Polylang functionality through hooks

**Security Features:**
* User authentication required
* Capability-based permissions (`manage_terms`, `edit_posts`)
* Nonce verification for CSRF protection
* Input sanitization and validation
* User permission filtering

**Extensibility:**
* 20+ action hooks for custom functionality
* 15+ filter hooks for data modification
* Component modification capabilities
* Plugin lifecycle hooks

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure Polylang plugin is installed and activated.
4. Use the REST API endpoint `/wp-json/4wp-polylang-api-sync/v1/{taxonomy|posts}`.

== Frequently Asked Questions ==

= What is this plugin for? =

This plugin provides a REST API for synchronizing Polylang translations. It's useful when you need to programmatically link content between languages or integrate with external systems.

= Is this plugin secure? =

Yes! The plugin includes comprehensive security measures:
* Authentication required for all endpoints
* Proper capability checks
* CSRF protection via nonces
* Input validation and sanitization

= Can I extend the plugin functionality? =

Absolutely! The plugin provides extensive hooks and filters:
* Action hooks for custom logic
* Filter hooks for data modification
* Component modification capabilities
* Plugin lifecycle hooks

= Where can I find the logs? =

When WP_DEBUG is enabled, logs are written to `/wp-content/debug.log` with the prefix `[4WP Polylang Sync]`.

= Does this work with Polylang Pro? =

Yes, this plugin works with both Polylang Free and Pro versions.

== Screenshots ==

1. REST API endpoint documentation
2. Security features overview
3. Hooks and filters documentation
4. Example API responses

== Changelog ==

= 1.1.0 =
* Complete plugin rewrite with professional architecture
* Added comprehensive security features
* Implemented extensive hooks and filters system
* Added proper error handling and validation
* Integrated WordPress debug.log for audit trails
* Added PSR-4 compatible autoloader
* Improved documentation and examples
* Fixed critical security vulnerabilities

= 1.0.0 =
* Initial release with basic structure
* Basic REST API endpoints
* Placeholder functionality (not recommended for production)

== Upgrade Notice ==

= 1.1.0 =
This is a major update that completely rewrites the plugin. Please test thoroughly before upgrading in production. The new version includes significant security improvements and extensibility features.

== Roadmap ==

**Version 1.2.0 (Planned):**
* Rate limiting and DoS protection
* Enhanced logging system with admin panel
* Performance optimizations

**Version 1.3.0 (Planned):**
* Batch operations and caching
* Advanced analytics and monitoring
* WooCommerce and ACF integration

**Version 2.0.0 (Future):**
* Multi-site support
* GraphQL endpoints
* Enterprise features

== Support ==

For support and feature requests, please visit our website at https://4wp.dev or check the comprehensive documentation in the plugin's README.md file.
