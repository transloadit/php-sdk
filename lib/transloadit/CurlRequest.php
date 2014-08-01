<?php
namespace transloadit;

class CurlRequest {
  private static $curlEnvironmentOptions = array();
  public $method = 'GET';
  public $url = null;
  public $headers = array();
  public $fields = array();
  public $files = array();

  // Apply all passed attributes to the instance
  public function __construct($attributes = array()) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function getCurlOptions() {
    $url = $this->url;

    $hasBody = ($this->method === 'PUT' || $this->method === 'POST');
    if (!$hasBody) {
      $url .= '?'.http_build_query($this->fields);
    }

    $options = $curlEnvironmentOptions + array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $this->method,
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => $this->headers,
    );

    if ($hasBody) {
      $fields = $this->fields;
      foreach ($this->files as $field => $file) {
        if (!file_exists($file)) {
          trigger_error('File ' . $file . ' does not exist', E_USER_ERROR);
          return false;
        }
        if (is_int($field)) {
          $field = 'file_'.($field+1);
        }
        $fields[$field] = '@'.$file;
      }
      $options[CURLOPT_POSTFIELDS] = $fields;
    }

    return $options;
  }

  public function execute($response = null) {
    $curl = curl_init();

    curl_setopt_array($curl, $this->getCurlOptions());

    if (!$response) {
      $response = new CurlResponse();
    }
    $response->data = curl_exec($curl);
    $response->curlInfo = curl_getinfo($curl);
    $response->curlErrorNumber= curl_errno($curl);
    $response->curlErrorMessage = curl_error($curl);

    curl_close($curl);

    return $response;
  }
  
  public static function setCurlEnvironmentOptions(array $curlEnvironmentOptions = array()) {
    self::$curlEnvironmentOptions = $curlEnvironmentOptions;
  }
}
