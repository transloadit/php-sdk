<?php
require_once(dirname(__FILE__).'/HttpResponse.php');

class HttpRequest{
  public $method = 'GET';
  public $url = null;
  public $headers = array();
  public $fields = array();

  // Apply all passed attributes to the instance
  public function __construct($attributes = array()) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  // Sets the value for a field to be submitted to transloadit
  public function setField($name, $value) {
    $this->fields[$name] = $value;
  }

  // Sets the value for a field to be submitted to transloadit
  public function setFile($name, $path) {
    $this->setField($name, '@'.$path);
  }

  // Adds a file to the request using a unique field name
  public function addFile($path) {
    // Determine the number of files already attached to this request to
    // come up with a unique field name for it.
    $fileNumber = 1;
    foreach ($this->fields as $field => $value) {
      if ($value[0] === '@') {
        $fileNumber++;
      }
    }

    $this->setFile('file_'.$fileNumber, $path);
  }

  public function getCurlOptions() {
    $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $this->method,
      CURLOPT_URL => $this->url,
      CURLOPT_HTTPHEADER => $this->headers,
      CURLOPT_POSTFIELDS => $this->fields,
    );

    return $options;
  }

  public function execute() {
    $curl = curl_init();

    curl_setopt_array($curl, $this->getCurlOptions());

    $response = new HttpResponse();
    $response->data = curl_exec($curl);
    $response->curlInfo = curl_getinfo($curl);
    $response->curlErrorNumber= curl_errno($curl);
    $response->curlErrorMessage = curl_error($curl);

    curl_close($curl);

    return $response;
  }
}
?>
