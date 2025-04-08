<?php
/**
 * Plugin Name: WP SQLite Console
 * Plugin URI: https://github.com/yourusername/wp-sqlite-console
 * Description: A secure SQLite management console for WordPress root users
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-sqlite-console
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPSQLC_VERSION', '1.0.0');
define('WPSQLC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSQLC_PLUGIN_URL', plugin_dir_url(__FILE__));

class WP_SQLite_Console {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'check_user_access'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wpsqlc_execute_query', array($this, 'execute_query'));
        add_action('wp_ajax_wpsqlc_get_db_structure', array($this, 'get_db_structure'));
    }

    public function check_user_access() {
        if (isset($_GET['page']) && $_GET['page'] === 'wp-sqlite-console') {
            // Check if user is root (ID = 1)
            if (!current_user_can('administrator') || get_current_user_id() !== 1) {
                wp_die(__('Access Denied. Only the root administrator can access this page.', 'wp-sqlite-console'));
            }
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('SQLite Console', 'wp-sqlite-console'),
            __('SQLite Console', 'wp-sqlite-console'),
            'administrator',
            'wp-sqlite-console',
            array($this, 'render_admin_page'),
            'dashicons-database',
            80
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_wp-sqlite-console') {
            return;
        }

        wp_enqueue_style('wp-codemirror');
        wp_enqueue_script('wp-codemirror');
        wp_enqueue_script('csslint');
        wp_enqueue_script('jshint');
        wp_enqueue_script('jsonlint');
        wp_enqueue_style(
            'wpsqlc-admin-style',
            WPSQLC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WPSQLC_VERSION
        );
        wp_enqueue_script(
            'wpsqlc-admin-script',
            WPSQLC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-codemirror'),
            WPSQLC_VERSION,
            true
        );
        wp_localize_script('wpsqlc-admin-script', 'wpsqlc', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpsqlc_nonce')
        ));
    }

    public function render_admin_page() {
        if (!current_user_can('administrator') || get_current_user_id() !== 1) {
            wp_die(__('Access Denied. Only the root administrator can access this page.', 'wp-sqlite-console'));
        }
        include WPSQLC_PLUGIN_DIR . 'views/admin-page.php';
    }

    public function get_db_structure() {
        check_ajax_referer('wpsqlc_nonce', 'nonce');

        if (!current_user_can('administrator') || get_current_user_id() !== 1) {
            wp_send_json_error('Access denied');
        }

        global $wpdb;
        $tables_query = "SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
        $tables = $wpdb->get_results($tables_query);

        $structure = array();
        foreach ($tables as $table) {
            $columns_query = "PRAGMA table_info(" . $wpdb->_escape($table->name) . ")";
            $columns = $wpdb->get_results($columns_query);
            $structure[$table->name] = array(
                'columns' => $columns,
                'create_sql' => $table->sql
            );
        }

        wp_send_json_success($structure);
    }

    public function execute_query() {
        check_ajax_referer('wpsqlc_nonce', 'nonce');


        if (!current_user_can('administrator') || get_current_user_id() !== 1) {
            wp_send_json_error('Access denied');
        }

        $query = isset($_POST['query']) ? trim($_POST['query']) : '';
        if (empty($query)) {
            wp_send_json_error('Query is empty');
        }

        try {
            global $wpdb;
            $result = $wpdb->get_results($query, ARRAY_A);
            wp_send_json_success(array(
                'result' => $result,
                'message' => 'Query executed successfully'
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}

// Initialize the plugin
function wpsqlc_init() {
    WP_SQLite_Console::get_instance();
}
add_action('plugins_loaded', 'wpsqlc_init');