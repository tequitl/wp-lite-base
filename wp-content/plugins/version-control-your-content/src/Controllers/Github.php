<?php
namespace VCYC\Controllers;

use VCYC\Models\Connections;

/**
 * Github Controller
 * @package VCYC\Controllers
 * @since 1.0.0
 */
class Github {
    private $active_conn;
    
    public function __construct() {

      //Enqueue all github scripts
      add_action('admin_enqueue_scripts', array($this, 'enqueue_github_scripts'));

        //For customizer
        add_action('customize_controls_print_footer_scripts', array($this, 'github_params'));
        //For other admin pages
        add_action('admin_footer', array($this, 'github_params'));
       
        add_action('admin_bar_menu', array($this, 'add_github_api_usage_menu'), 100);
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_global_script'));
    }

    public static function enqueue_admin_global_script() {
        // Enqueue a global script for all admin pages
       wp_enqueue_script(Plugin::$name.'-global', Plugin::$assets_url . 'js/global.js', array(), Plugin::$version, true);
       wp_enqueue_style( Plugin::$name."-global", Plugin::$assets_url . 'css/global.css', array(), Plugin::$version, 'all' );

        // Prepare the inline script data
        $active_conn = Connections::get_active_connection();
        $active_conn_json = $active_conn ? wp_json_encode((array)$active_conn) : '{}';
        $labels = Labels::get_all_labels();
        $icon_url = Plugin::$assets_url . 'img/git.svg';
        $current_user = wp_get_current_user();
        $user_display_name = esc_js($current_user->display_name);
        $labels = array_map('esc_js', $labels);

        $script_data = array(
            'user' => $user_display_name,
            'labels' => $labels,
            'auth' => array(
                'nonce' => wp_create_nonce('wp_rest'),
                'rest_root' => esc_url_raw(rest_url())
            ),
            'git_conn' => json_decode($active_conn_json),
            'icon_url' => esc_url($icon_url),
            'admin_url' => esc_url(admin_url())
        );

        // Add the inline script
        wp_add_inline_script( 
            Plugin::$name.'-global', // Your script handle
            'var vcyc = ' . wp_json_encode($script_data) . ';',
            'before'
        );
    }

    public function enqueue_github_scripts() {
      /* 
      Enqueue the usage quota script. This script is used to display the usage quota and the quota resets in the admin bar menu. It needs to be loaded in all admin pages.
      */
      Scripts::enqueue_js_module('usage-quota', 'github/usage-quota.js');
      //Enqueue the script
      //Scripts::enqueue_js_module('github-init', 'github/init.js');
      //Scripts::enqueue_js_module('github-commits-box', 'github/commits-box.js');
      //Scripts::enqueue_js_module('github-new-connection', 'github/new-connection.js');

    }

    public function github_params(){
      //Prepare the inline script
      $this->active_conn = Connections::get_active_connection();
      if($this->active_conn) $active_conn_json = wp_json_encode((array)$this->active_conn);
      else $active_conn_json = '{}';
      $labels=Labels::get_all_labels();
      $icon_url=Plugin::$assets_url.'img/git.svg';
      //Get current user display name
      $current_user = wp_get_current_user();
      $user_display_name = esc_js($current_user->display_name);
      $user_display_name = esc_js($user_display_name);
      $labels = array_map('esc_js', $labels);
      
      $script_data = array(
          'user' => $user_display_name,
          'labels' => $labels,
          'auth' => array(
              'nonce' => wp_create_nonce('wp_rest'),
              'rest_root' => esc_url_raw(rest_url())
          ),
          'git_conn' => json_decode($active_conn_json),
          'icon_url' => esc_url($icon_url),
          'admin_url' => esc_url(admin_url())
      );
  
      wp_add_inline_script(
          'admin-page-vcyc', // Your script handle
          'var window.vcyc = ' . wp_json_encode($script_data) . ';',
      );
    }   

    public function add_github_api_usage_menu($wp_admin_bar) {

      $active_conn = Connections::get_active_connection();
      if(!$active_conn) return;

        // Add a new menu item to the admin bar with a custom class
        // 'title' => '<img src="' . Plugin::$assets_url . 'img/git.svg" alt="GitHub Icon" style="vertical-align: middle; margin-right: 5px;">Usage Quota: <span class="github-api-rate-limits"> </span>',
        $wp_admin_bar->add_node(array(
            'id'    => 'github_api_rate_limits',
            'title' => '<span class="github-api-rate-limits-icon"></span>Usage Quota:  <span class="github-api-rate-limits"> </span>',
            'href'  => false,
            'meta'  => array('class' => 'github-api-limits')
        ));

        // Add a submenu item under the 'github_api_limits' menu item
        $wp_admin_bar->add_node(array(
            'id'     => 'github_api_limits_details',
            'parent' => 'github_api_rate_limits',
            'title'  => 'Quota resets in: <span class="github-api-rate-limits-reset">0m 00s</span>',
            'href'   => false
        ));
    }
}
