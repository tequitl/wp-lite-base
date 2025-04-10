<?php
/**
 * Plugin Name: WP Music Player
 * Plugin URI: https://example.com/plugins/wp-music-player/
 * Description: A music player plugin with playlist management functionality.
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

// Register activation hook
register_activation_hook(__FILE__, 'wp_music_player_activate');

/**
 * Plugin activation function
 */
function wp_music_player_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create playlists table
    $table_name = $wpdb->prefix . 'music_playlists';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        songs longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Add menu item to the admin menu
 */
function wp_music_player_admin_menu() {
    add_menu_page(
        'Music Player', // Page title
        'Music Player', // Menu title
        'manage_options', // Capability required
        'wp-music-player', // Menu slug
        'wp_music_player_admin_page', // Callback function
        'dashicons-playlist-audio', // Icon
        30 // Position
    );
}
add_action('admin_menu', 'wp_music_player_admin_menu');

/**
 * Register scripts and styles
 */
function wp_music_player_enqueue_scripts() {
    // Admin scripts and styles
    if (is_admin()) {
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-music-player-admin', plugins_url('css/admin.css', __FILE__));
        wp_enqueue_media();
        wp_enqueue_script('wp-music-player-admin', plugins_url('js/admin.js', __FILE__), array('jquery', 'media-upload', 'thickbox'), '1.0.0', true);
        wp_localize_script('wp-music-player-admin', 'wpMusicPlayer', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_music_player_nonce')
        ));
    } else {
        // Frontend scripts and styles
        wp_enqueue_style('wp-music-player', plugins_url('css/style.css', __FILE__), array('dashicons'));
        wp_enqueue_script('wp-music-player', plugins_url('js/player.js', __FILE__), array('jquery'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'wp_music_player_enqueue_scripts');
add_action('wp_enqueue_scripts', 'wp_music_player_enqueue_scripts');

/**
 * Register shortcode
 */
function wp_music_player_shortcode($atts) {
    $atts = shortcode_atts(array(
        'playlist_id' => 0
    ), $atts);
    
    if (!$atts['playlist_id']) {
        return '<p>Please specify a playlist ID.</p>';
    }
    
    ob_start();
    include(plugin_dir_path(__FILE__) . 'templates/player.php');
    return ob_get_clean();
}
add_shortcode('music_player', 'wp_music_player_shortcode');

/**
 * Include required files
 */
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';