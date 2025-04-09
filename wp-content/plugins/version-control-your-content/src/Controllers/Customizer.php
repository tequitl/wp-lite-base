<?php
namespace VCYC\Controllers;

class Customizer {

  public function __construct() {
    // Hook to enqueue scripts for the customizer
    add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_customizer_script'));
    // Hook to enqueue styles for the customizer
    add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_customizer_style')); 
  }

  public function enqueue_customizer_script() {
    Scripts::enqueue_js_module('customizer-sync', 'customizer.js');
  }

  public function enqueue_customizer_style() {

    // Enqueue the commits box style
    wp_enqueue_style(
      'vcyc-commits-box-style',
      Plugin::$assets_url . 'css/commits-box.css',
      array(),
      Plugin::$version
    );
    // Enqueue the customizer style
    wp_enqueue_style(
        'vcyc-customizer-style',
        Plugin::$assets_url . 'css/customizer.css',
        array(),
        Plugin::$version
    );
  }
}//end of class