<?php
namespace transloadit;

class Transloadit{
  public $key      = null;
  public $secret   = null;
  public $endpoint = 'https://api2.transloadit.com';

  public function __construct($attributes = array()) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function request($options = array(), $execute = true) {
    $options = $options + array(
      'key'               => $this->key,
      'secret'            => $this->secret,
      'endpoint'          => $this->endpoint,
      'waitForCompletion' => false,
    );
    $request = new TransloaditRequest($options);
    return ($execute)
      ? $request->execute()
      : $request;
  }

  public static function response() {
    if (!empty($_POST['transloadit'])) {
      $json = $_POST['transloadit'];
      if (ini_get('magic_quotes_gpc') === '1') {
        $json = stripslashes($json);
      }

      $response = new TransloaditResponse();
      $response->data = json_decode($json, true);
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

  public function createAssemblyForm($options = array()) {
    $out = array();

    $customFormAttributes = array();
    if (array_key_exists('attributes', $options)) {
      $customFormAttributes = $options['attributes'];
      unset($options['attributes']);
    }

    $assembly = $this->request($options + array(
      'method' => 'POST',
      'path'   => '/assemblies',
    ), false);
    $assembly->prepare();

    $formAttributes = array(
      'action'  => $assembly->url,
      'method'  => $assembly->method,
      'enctype' => 'multipart/form-data',
    ) + $customFormAttributes;

    $formAttributeList = array();
    foreach ($formAttributes as $key => $val) {
      $formAttributeList[] = sprintf('%s="%s"', $key, htmlentities($val));
    }

    $out[] = '<form '.join(' ', $formAttributeList).'>';

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
    return $this->request($options + array(
      'method' => 'POST',
      'path'   => '/assemblies',
    ));
  }

  // Leave this in for BC.
  public function getAssembly($assembly_id) {
    $response = $this->request(array(
      'method' => 'GET',
      'path'   => '/assemblies/'.$assembly_id,
    ), true);

    return $response;
  }

  public function deleteAssembly($assembly_id) {
    return $this->cancelAssembly($assembly_id);
  }

  public function cancelAssembly($assembly_id) {
    // Look up the host for this assembly
    $response = $this->request(array(
      'method' => 'GET',
      'path'   => '/assemblies/'.$assembly_id,
    ), true);

    $error = $response->error();
    if ($error) {
      return $error;
    }

    $url = parse_url($response->data['assembly_url']);

    $response = $this->request(array(
      'method' => 'DELETE',
      'path'   => $url['path'],
      'host'   => $url['host'],
    ));

    $error = $response->error();
    if ($error) {
      return $error;
    } else {
      return $response;
    }
  }
}
