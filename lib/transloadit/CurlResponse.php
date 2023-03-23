<?php

namespace transloadit;

class CurlResponse {
  public $data = null;

  public $curlInfo = null;
  public $curlErrorNumber = null;
  public $curlErrorMessage = null;

  // Apply all passed attributes to the instance
  public function __construct($attributes = []) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function parseJson() {
    $decoded = json_decode($this->data, true);
    if (!is_array($decoded)) {
      return false;
    }

    $this->data = $decoded;
    return true;
  }

  public function error() {
    if (!$this->curlErrorNumber) {
      return false;
    }

    return sprintf(
      'curl: %d: %s',
      $this->curlErrorNumber,
      $this->curlErrorMessage
    );
  }
}
