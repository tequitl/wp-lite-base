<?php
namespace VCYC\Controllers;

/**
 * Scripts Controller - Handles JavaScript module loading across different WordPress versions
 * @package VCYC\Controllers
 * @since 1.0.0
 */
class Scripts {    
    public function __construct() {
        
    }

    /**
     * Enqueues JavaScript modules with compatibility for both WP 6.5+ and older versions
     * 
     * @param string $script_name The name of the script (used in handle generation)
     * @param string $script_path The path to the script file relative to assets/js/
     * 
     * @return void
     */
    
    public static function enqueue_js_module($script_name, $script_path){
        // Generate a unique handle for the script using plugin name prefix
        $handle = Plugin::$name . "-" . $script_name;
        
        // Construct the full URL path to the script
        $url = Plugin::$assets_url . "js/" . $script_path;

        // WordPress 6.5+ has native support for ES modules
        if(function_exists('wp_enqueue_script_module')){
            // Use the new wp_enqueue_script_module function
            wp_enqueue_script_module( 
                $handle,
                $url, 
                array( 'jquery' ),  // Dependencies
                Plugin::$version,   // Version number
                false              // Load in header
            );
        }
        // Fallback for WordPress versions before 6.5
        else {
            // Enqueue script normally
            wp_enqueue_script(
                $handle,
                $url,
                array( 'jquery' ),  // Dependencies
                Plugin::$version,   // Version number
                false              // Load in header
            );

            // Add type="module" attribute to script tag for ES module support
            add_filter('script_loader_tag', function($tag, $loader_handle) use ($handle) {
                if ($loader_handle === $handle) {
                    // Add type="module" to the script tag
                    return str_replace(' src=', ' type="module" src=', $tag);
                }
                return $tag;
            }, 10, 2);
        }
    }  
}
