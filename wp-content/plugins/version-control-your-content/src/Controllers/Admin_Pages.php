<?php 

namespace VCYC\Controllers;
use VCYC\Views\Admin_Pages as Admin_views;

class Admin_Pages{
  public function __construct() {
    add_action('admin_menu', array($this, 'connections_menu'));
    add_action('admin_init', array($this, 'add_version_control_section'));
  }

  public function connections_menu() {
    add_menu_page(
      'Version Control your Content',  // Page title
      'Version Control',                   // Menu title
      'manage_options',               // Capability
      'vcyc-connections',              // Menu slug
      array($this, 'connections_page'),// Callback function
      Plugin::$assets_url . 'img/git.svg', // Path to the SVG icon
      100                               // Position
  );

  // Add the submenu page
  add_submenu_page(
      'vcyc-connections',             // Parent slug
      'How it works?',               // Page title
      'How it works?',               // Menu title
      'manage_options',                // Capability
      'vcyc-how-it-works',         // Menu slug
      array($this, 'how_it_works_page') // Callback function
  );
  }

  public function connections_page() {
    if (!current_user_can('manage_options')) {
      echo '<div class="wrap"><h1>Error</h1><p>Only Admins can access this page.</p></div>'; // Fallback error message
      return;
  }
  /**
   * Loading view in this way ensures that only the needed assets are loaded on this page
   */
  $page = new Admin_views\Connections();
  $page->css();
  $page->html();
  $page->js();
  
  }

  public function how_it_works_page() { ?>

<div class="wrap vcyc-wrap how-it-works"> 
    <h2 style="font-size: 30px; line-height:40px;">How it works?</h2>
    <p>Version Control Your Content provides an alternative to the native WP Revisions feature using Git services. It also enables version control for Additional CSS and Settings pages.</p>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Requirements</h2>
    <ul>
        <li>A <a href="https://github.com/join" target="_blank">GitHub account</a></li>
        <li>A <a href="https://github.com/new" target="_blank">GitHub private repository</a></li>
        <li>(Optional but recommended) A <a href="https://github.com/settings/tokens/new" target="_blank">GitHub fine-grained personal access token</a></li>
    </ul>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Supported Areas</h2>
    <p>This plugin provides version control for the following sections:</p>
    <ul>
        <li>Block Editor (Gutenberg)</li>
        <li>Classic Editor</li>
        <li>Additional CSS in Customizer</li>
        <li>Default Settings pages in wp-admin</li>
    </ul>
    <p>Each page will have a "Version Control" box where you can enable or disable version tracking. The top admin bar will also show real-time GitHub API usage.</p>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Technical Details</h2>
    <ol>
        <li>When you save a post or submit a form, JavaScript captures the data before sending it to the backend.</li>
        <li>The data is sanitized and converted to JSON format if needed.</li>
        <li>The JSON or HTML data is committed to your Git repository.</li>
        <li>Each change is stored as a new commit in your Git repo.</li>
        <li>The commit history is displayed in the admin panel, allowing you to view changes or revert versions.</li>
    </ol>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Planned Features</h2>
    <ul>
        <li>Integration with popular page builders like Elementor, Divi, Beaver Builder, WPBakery</li>
        <li>WooCommerce support</li>
        <li>Integration with BuddyPress and bbPress</li>
        <li>Support for WPML and other multilingual plugins</li>
    </ul>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Installation</h2>
    <ol>
        <li>Upload the plugin ZIP file and install it from "Add New Plugin" in wp-admin.</li>
        <li>Activate the plugin through the <strong>Plugins</strong> screen.</li>
        <li>Go to "Version Control" in wp-admin.</li>
        <li>Add and activate a new connection.</li>
        <li>"Version Control" boxes will appear in the post editor, Additional CSS, and Settings pages.</li>
        <li>A new version is created every time you press the save/submit button.</li>
    </ol>
    
    <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 5px;">Frequently Asked Questions</h2>
    <p><strong>What is version control?</strong></p>
    <p>Version control allows you to track content changes over time, revert to previous versions, and compare changes.</p>
    
    <p><strong>Is it free to use?</strong></p>
    <p>Yes, GitHub has offered free private repositories since January 2019. It should be sufficient for most small-to-medium websites.</p>
    
    <p><strong>Why version control settings pages?</strong></p>
    <p>Site owners frequently update settings like site title, tagline, and SEO-related details. This plugin automatically keeps a history of all these changes.</p>
    
    <p><strong>How is this different from activity monitor plugins?</strong></p>
    <p>This plugin does not store anything in your database, reducing performance overhead. Instead, it securely logs changes in your private Git repository.</p>
    
    <p><strong>Does this replace backup plugins?</strong></p>
    <p>No, it is not a backup replacement. It only tracks content changes.</p>
    
    <p><strong>Are there GitHub API limitations?</strong></p>
    <p>GitHub API allows 5000 requests per hour and a maximum repository size of 1GB, which is sufficient for most users.</p>
    
    <p><strong>Does it support Bitbucket or GitLab?</strong></p>
    <p>Currently, the plugin only works with GitHub. Future updates may add support for Bitbucket and GitLab.</p>
    
    <p><strong>Why use a personal access token instead of OAuth?</strong></p>
    <p>A personal access token gives you full control over repository access, allowing you to limit access to a single repository.</p>
    
    <p><strong>Why is my repository not showing up?</strong></p>
    <p>This plugin only works with private repositories for security reasons.</p>
    
</div>
    <?php
  }

  public function add_version_control_section() {
    // Define pages where we want to add the section
    $pages = ['general', 'reading', 'writing', 'discussion', 'media', 'permalink'];
    
    // Add version control section to each page
    foreach ($pages as $page) {
        add_settings_section(
            'options-'.$page,
            'Version Control',
            array($this, 'vcyc_section_callback'),
            $page
        );
    }
  }

  public function vcyc_section_callback($args) {
    // Get the current page ID from the args
    $page = $args['id'];
    //On settings pages
    echo '  <div id="vcyc-options-pages-box" data-options_page="'.esc_html($page).'"></div>';
        wp_enqueue_style(
          'vcyc-commits-box-style',
          Plugin::$assets_url . 'css/commits-box.css',
          array(),
          '1.0.0'
        );
        Scripts::enqueue_js_module('options-pages', 'options-pages.js');
  }

}