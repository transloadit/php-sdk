<?php
require_once(dirname(__FILE__).'/TransloaditRequest.php');

class Transloadit{
  public $key = null;
  public $secret = null;

  public function __construct($attributes = array()) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function request($options = array(), $execute = true) {
    $options = $options + array(
      'key' => $this->key,
      'secret' => $this->secret,
    );
    $request = new TransloaditRequest($options);
    return ($execute)
      ? $request->execute()
      : $request;
  }

  public function createAssembly($options) {
    $boredInstance = $this->request(array(
      'method' => 'GET',
      'path' => '/instances/bored',
    ), true);

    return $this->request($options + array(
      'method' => 'POST',
      'path' => '/assemblies',
      'host' => $boredInstance->data['api2_host'],
    ));
  }
}
?>
