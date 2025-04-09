<?php 
namespace VCYC\Models;

class Encryption{
  
  //private static $encryption_key="vcgfv0hnoi4ugkch3928hdgklasj58dhkoiu1yxsw";
  private static $encryption_key="gfv0hnoi4ugkch3928hdgklasj58dhkoiuyxsw";

  public function encrypt_connecions($connections){
    return $this->encrypt($connections);
  }

  public function decrypt_connections($connections){
    if(empty($connections)) return [];
    return $this->decrypt($connections);
  }

  private function encrypt($arr){
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt(wp_json_encode($arr), 'aes-256-cbc', self::$encryption_key, 0, $iv);
    $encrypt_step1=base64_encode($encrypted) . ':::' . base64_encode($iv);
    $encrypt_step2= esc_sql(maybe_serialize($encrypt_step1));
    return $encrypt_step2;
  }

  private static function decrypt($arr){
    if(empty($arr)) return json_decode([]);
    $conn_decoded = maybe_unserialize($arr);
    $conn_decoded = explode(':::', $conn_decoded);
    $iv = base64_decode($conn_decoded[1]);
    $decrypted = openssl_decrypt(base64_decode($conn_decoded[0]), 'aes-256-cbc', self::$encryption_key, 0, $iv);
    return (array)json_decode($decrypted);
  }
}