=== 4WP Polylang API Sync ===
Contributors: 4wp.dev
Tags: polylang, api, sync, translations, 4wp
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a custom REST API endpoint for synchronizing taxonomy and post translations in Polylang.

== Description ==
This plugin creates a single REST API route to synchronize taxonomy terms and posts translations between languages in Polylang. Requires the user to be authenticated and have the proper capabilities.

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the REST API endpoint `/wp-json/4wp-polylang-sync/v1/{taxonomy|posts}`.

== Changelog ==
= 1.0.0 =
* Initial release
