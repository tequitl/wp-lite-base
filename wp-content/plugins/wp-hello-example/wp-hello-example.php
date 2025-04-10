<?php
/**
 * Plugin Name: WP Hello Example
 * Plugin URI: https://example.com/plugins/wp-hello-example/
 * Description: A simple plugin that displays Hello World in the WordPress admin area.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add menu item to the admin menu
 */
function wp_hello_example_admin_menu() {
    add_menu_page(
        'Hello Example', // Page title
        'Hello Example', // Menu title
        'manage_options', // Capability required
        'wp-hello-example', // Menu slug
        'wp_hello_example_admin_page', // Callback function
        'dashicons-admin-generic', // Icon
        0 // Position
    );
}
add_action('admin_menu', 'wp_hello_example_admin_menu');

/**
 * Render the admin page content
 */
function wp_hello_example_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="card" style="padding: 20px; max-width: 600px;">
            <img src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExNnE2M3BvdWRsdWprMTIzanFkZmZuYTlwNm1rbmt6a29sNGFoeGtuOCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/l0MYz7QMC5w2S90Yw/giphy.gif" 
                 alt="South Park's Cartman animated GIF" 
                 style="max-width: 100%; height: auto; display: block; margin: 0 auto 20px auto;">
            <h2>eSEPA!</h2>
            <p>uNEETE</p>
            <p>This is a simple example of a WordPress admin page.</p>
        </div>
    </div>
    <?php
}