<?php
class HttpResponse{
  public $data = null;

  public $curlInfo = null;
  public $curlErrorNumber = null;
  public $curlErrorMessage = null;

  // Apply all passed attributes to the instance
  public function __construct($attributes = array()) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function parseJson() {
    $this->data = json_decode($this->data, true);
  }
}
?>
