<?php 
namespace VCYC\Views\Admin_Pages;

use VCYC\Controllers\Plugin;
use VCYC\Models\Connections as Conn_Model;
use VCYC\Controllers\Labels;
use VCYC\Controllers\Scripts;
/**
 * Connections page view
 */

 class Connections{

  public function __construct() {
   
  }
  public function css(){
    wp_enqueue_style( Plugin::$name."-connections-page-css", Plugin::$assets_url . 'css/admin-page.css', array(), Plugin::$version, 'all' );

  }
  public function html() {
    $conns = Conn_Model::get_connections();
    $active_conn = Conn_Model::get_active_connection();
    ?>
  <main class="wrap vcyc">
  <div class="heading-wrap">
    <h1>Version Control your Content</h1><h2>(Alternative to WP Revisions)</h2>
  </div>
  <div class="content-wrap">
  <section class="left-content">
    <?php 

    if (!$active_conn){ 
      ?>
      <div class="no-connection vcyc-msg vcyc-error"><?php Labels::print('no_conn_error'); ?></div>
    <?php
    
    } ?>
    <section class="connections-container">
    <?php 
    foreach ($conns as $conn) {
      if($active_conn && $conn->id == $active_conn->id) $checked='checked';
      else $checked='';
      $account_url='https://github.com/'.esc_url($conn->account);
      $repo_url= $account_url.'/'.esc_url($conn->repo);
      $branch_url= $repo_url.'/tree/'.esc_url($conn->branch);
      echo '<div class="connection-card">'
      .'<dl class="connection-info">'
      .'<dt>Active</dt><dd><label class="switch"><input class="active-connection" data-id="'.esc_attr($conn->id).'" type="checkbox" '. esc_attr($checked).'><span class="slider round"></span></label></dd>'
      .'<dt>Name</dt><dd>'.esc_html($conn->name).' </dd>'
      .'<dt>Account</dt><dd>'.esc_html($conn->account).' <a href="'.esc_url($account_url).'" target="_blank"> <span class="dashicons dashicons-external"></span></a></dd>'
      .'<dt>Repository</dt><dd>'.esc_html($conn->repo).' <a href="'.esc_url($repo_url).'" target="_blank"> <span class="dashicons dashicons-external"></span></a></dd>'
      .'<dt>Branch</dt><dd>'.esc_html($conn->branch).' <a href="'.esc_url($branch_url).'" target="_blank"> <span class="dashicons dashicons-external"></span></a></dd>'
      .'<dd><a class="delete-connection" style="padding-left: 0px;" data-name="'.esc_attr($conn->name).'" data-id="'.esc_attr($conn->id).'" href="#">Delete</a></dd>'
      .'</dl>'
      .'<svg class="card-bg" height="1024" width="1024" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><g fill="#1f2328"><path d="M512 0C229.25 0 0 229.25 0 512c0 226.25 146.688 418.125 350.156 485.812 25.594 4.688 34.938-11.125 34.938-24.625 0-12.188-.469-52.562-.719-95.312C242 908.812 211.906 817.5 211.906 817.5c-23.312-59.125-56.844-74.875-56.844-74.875-46.531-31.75 3.53-31.125 3.53-31.125 51.406 3.562 78.47 52.75 78.47 52.75 45.688 78.25 119.875 55.625 149 42.5 4.654-33 17.904-55.625 32.5-68.375-113.656-12.937-233.218-56.875-233.218-253.063 0-55.938 19.969-101.562 52.656-137.406-5.219-13-22.844-65.094 5.062-135.562 0 0 42.938-13.75 140.812 52.5 40.812-11.406 84.594-17.031 128.125-17.219 43.5.188 87.312 5.875 128.188 17.281 97.688-66.312 140.688-52.5 140.688-52.5 28 70.531 10.375 122.562 5.125 135.5 32.812 35.844 52.625 81.469 52.625 137.406 0 196.688-119.75 240-233.812 252.688 18.438 15.875 34.75 47 34.75 94.75 0 68.438-.688 123.625-.688 140.5 0 13.625 9.312 29.562 35.25 24.562C877.438 930 1024 738.125 1024 512 1024 229.25 794.75 0 512 0z"/></g></svg>'
      .'</div><!--.connection-card-->';
    }
    ?>
     <a class="connection-card add-new">
        <span class="dashicons dashicons-insert"></span>
        <span class="git-con-text">Connect New Git Source</span>
    </a>
    </section><!--.connections-container-->
    </section><!--.left-content-->

    <aside class="right-sidebar">
      <section class="vcyc-msg vcyc-info donation">
        <h3>Help me in Development?</h3>
        <p>Hi, I am the developer of this plugin. If you like this plugin and want to help me in development, you can buy me a Coffee.</p>
        <a href="https://www.buymeacoffee.com/harisamjed" target="_blank" class="bmac-button"></a>
      </section>
    
    </aside><!--.right-sidebar-->
  </div><!--.-->
  </main>
  <?php
}

  public function js(){
    /**
     * This plugin uses moduler JS
     * 
     */
    Scripts::enqueue_js_module('admin-page', 'admin-pages/admin-page.js');
    
  }

}//class END