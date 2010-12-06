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

  public static function response() {
    if (!empty($_POST['transloadit'])) {
      $response = new TransloaditResponse();
      $response->data = json_decode($_POST['transloadit'], true);
      return $response;
    }

    if (!empty($_GET['assembly_url'])) {
      $request = new TransloaditRequest(array(
        'url' => $_GET['assembly_url'],
      ));
      return $request->execute();
    }
    return false;
  }

  public function form($options) {
    $out = array();

    $assembly = $this->request($options + array(
      'method' => 'POST',
      'path' => '/assemblies',
    ), false);
    $assembly->prepare();

    $out[] = sprintf(
      '<form action="%s" method="%s" enctype="%s">',
      $assembly->url,
      $assembly->method,
      'multipart/form-data'
    );

    foreach ($assembly->fields as $field => $val) {
      $out[] = sprintf(
        '<input type="%s" name="%s" value="%s">',
        'hidden',
        $field,
        htmlentities($val)
      );
    }

    return join("\n", $out);
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
