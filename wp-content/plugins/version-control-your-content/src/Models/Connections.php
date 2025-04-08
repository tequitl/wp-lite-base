<?php 
namespace VCYC\Models;

use VCYC\Models\Encryption;

class Connections{

  public static function get_connections(){
    $enc = new Encryption();
    $encrypted_conns=get_option('vcyc_github_conn');
    $decrypted_conns=$enc->decrypt_connections($encrypted_conns);
    return $decrypted_conns;
  }

  public static function get_active_connection_id(){
    $active_conn=get_option('vcyc_github_conn_active');
    return $active_conn;
  }

  public static function get_active_connection(){
    $active_conn_id=self::get_active_connection_id();
    $all_conns=self::get_connections();
    foreach ($all_conns as $conn) {
      if($conn->id==$active_conn_id) return $conn;
    }
    return false;
  }


  public static function add_new_connection($conn){
    $enc = new Encryption();
    $all_conns=array();
    $existing = get_option('vcyc_github_conn');
    if(!empty($existing)) $all_conns = $enc->decrypt_connections($existing);
    $all_conns[]=$conn;
    $conn_encrypted = $enc->encrypt_connecions($all_conns);
    update_option('vcyc_github_conn', $conn_encrypted);
  }
  public static function delete_connection($conn_id){
    $enc = new Encryption();
    $all_conns=array();
    $existing = get_option('vcyc_github_conn');
    if(!empty($existing)) $all_conns = $enc->decrypt_connections($existing);
    foreach ($all_conns as $key => $conn) {
      if($conn->id==$conn_id) unset($all_conns[$key]);
    }
    $conn_encrypted = $enc->encrypt_connecions($all_conns);
    update_option('vcyc_github_conn', $conn_encrypted);
  }


  public static function activate_connection($conn_id){
    if(!empty($conn_id)) update_option('vcyc_github_conn_active', $conn_id);
  }

  public static function deactivate_connection($conn_id){
    if(!empty($conn_id)) {
      if(get_option('vcyc_github_conn_active')==$conn_id) update_option('vcyc_github_conn_active', 'OFF');
    }
  }

  

}