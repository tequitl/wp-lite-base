<?php

/**
 * The core plugin class which Initializes plugin 
 *
 * @link       https://vcyc.harisamjed.pro
 * @since      1.0.0
 * @package    VCYC
 * @author     Haris Amjed <harisamjed@gmail.com>
 *
 * 
 */
namespace VCYC\Controllers;

class Plugin {

  /**
   * The name of the plugin
   */
  public static $name='vcyc';

  /**
   * The version of the plugin
   * Following this versioning system https://semver.org
  * Will be updated with every new release.
  */
  public static $version='1.0.0';

  /**
   * Set these variables to use in other classes
   */
  public static $file, $dir,$dir_url, $assets_url;
  /**
   * Initialize the class
   * 
   * @param string $plugin_file of the plugin
   * @param string $plugin_dir of the plugin
   */
  public function __construct($file, $dir) {
    self::$file = $file;
    self::$dir = $dir;
    self::$dir_url = plugin_dir_url($file);
    self::$assets_url=  self::$dir_url . 'assets/';
    $this->activate_deactivate();
    $this->i18n();
    $this->add_settings_link();
    $this->scripts();
    $this->rest_api();
    $this->github();
    $this->admin_pages();
    $this->meta_boxes();
    //$this->block();
    $this->customizer();
  }

  public function add_settings_link(){
    add_filter('plugin_action_links_' . plugin_basename(self::$file), array($this, 'settings_link'));
  }
  public function settings_link($links) {
    $settings_link2 = '<a href="admin.php?page=vcyc-connections">Settings</a>';
    $settings_link = '<a href="admin.php?page=vcyc-how-it-works">How it works</a>';
    
    array_unshift($links, $settings_link, $settings_link2);
    return $links;
  }
  private function scripts(){
    new Scripts();
  }
  private function github() {
    new Github();
  }

  private function admin_pages() {
    new Admin_Pages();
  }

  

  private function rest_api() {
    new Rest_Api();
  }

  private function meta_boxes() {
    new Meta_Box();
  }

  private function block() {
    //Block will load from meta box as well
    //new Block(); //commented out due to js errors
  }
  private function customizer() {
    new Customizer();
  }
  
  public function activate_deactivate() {
    register_activation_hook(self::$file, array($this, 'activate'));
    register_deactivation_hook(self::$file, array($this, 'deactivate'));
  }
  public function activate() {
    // Nothing to do upon plugin activation for now
  }
  public function deactivate() {
    // Nothing to do upon plugin deactivation for now
  }

  private function i18n() {
    // Load plugin text domain
    add_action('plugins_loaded', array($this, 'load_textdomain'));
  }
  public function load_textdomain() {
    load_plugin_textdomain(self::$name, false,self::$dir. '/i18n/languages/'
    );
  }
}//End of class Plugin