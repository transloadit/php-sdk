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

  public function createAssembly($options) {
    // Get ourselves a bored instance. This is better than relying on the
    // default load balancing since it's aware of the current server load
    // of each machine.
    $instanceRequest = new TransloaditRequest(array(
      'key' => $this->key,
      'secret' => $this->secret,
      'method' => 'GET',
      'path' => '/instances/bored',
    ));
    $instance = $instanceRequest->execute();

    // Setup the options for our assembly request, then send it off
    // and return the response.
    $options = array_merge(
      array(
        'key' => $this->key,
        'secret' => $this->secret,
        'method' => 'POST',
        'path' => '/assemblies',
        'host' => $instance->data['api2_host'],
      ),
      $options
    );

    $assemblyRequest = new TransloaditRequest($options);
    return $assemblyRequest->execute();
  }
}
?>
