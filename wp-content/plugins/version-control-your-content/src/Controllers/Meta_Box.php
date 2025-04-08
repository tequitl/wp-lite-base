<?php
namespace VCYC\Controllers;

use VCYC\Controllers\Plugin;
use VCYC\Controllers\Github;
//Note: Latest approach is to have this mietbox read and reuse the code for blocks editor and also use for elementor and other page builders
/**
 * Meta Box Controller
 * @package VCYC\Controllers
 * @since 1.0.0
 */
class Meta_Box {
  public function __construct() {
    add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_meta_box_script'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_meta_box_style'));
    add_action('save_post', array($this, 'save_meta_box')); // Add action to save the meta box data
  }
  
  public function add_meta_boxes() {
    $post_types = get_post_types(array('public' => true), 'names');
    foreach ($post_types as $post_type) {
        add_meta_box(
            'vcyc_meta_box',           // Unique ID
            'Version Control',          // Box title (updated title without "Settings")
            array($this, 'render_meta_box'), // Callback function
            $post_type,                 // Post type (dynamic for all public post types)
            'side',                     // Context (normal, side, advanced)
            'default'                   // Priority
        );
    }
  }

  public function render_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('vcyc_meta_box', 'vcyc_meta_box_nonce');
    ?>
    <div id="vcyc-meta-box"></div>

    <input type="hidden" id="vcyc-active-input" name="vcyc_active" 
    value="<?php echo esc_html(get_post_meta($post->ID, 'vcyc_active', true)); ?>" />
    <?php
}

  // Add method to save the active state
  public function save_meta_box($post_id) {
    if (!isset($_POST['vcyc_meta_box_nonce'])) return;
    $nonce = sanitize_text_field(wp_unslash($_POST['vcyc_meta_box_nonce'])); // Unslash and sanitize nonce
    if (!wp_verify_nonce($nonce, 'vcyc_meta_box')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['vcyc_active'])) {
       $active = sanitize_text_field(wp_unslash($_POST['vcyc_active'])); // Unslash and sanitize input
       update_post_meta($post_id, 'vcyc_active', $active); // Serialize the input before saving
    }
  }

  //Add js file for the meta box.
  public function enqueue_meta_box_script() {
    $screen = get_current_screen();
    if ($screen && $screen->base == 'post') { // Check if the current screen is one of the public post types
      wp_enqueue_script_module(
        'vcyc-meta-box-script',
        Plugin::$dir_url . 'assets/js/Meta_Box.js',
        array('jquery'),
        null,
        true
      );
    }
  } 

  // Add CSS file for the meta box
  public function enqueue_meta_box_style() {
    $screen = get_current_screen();
    $post_types = get_post_types(array('public' => true), 'names');
    if (in_array($screen->post_type, $post_types)) { // Check if the current screen is one of the public post types
      wp_enqueue_style(
        'vcyc-commits-box-style',
        Plugin::$assets_url . 'css/commits-box.css',
        array(),
        '1.0.0'
      );
    }
  }
}
