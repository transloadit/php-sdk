<?php
namespace transloadit;

class TransloaditResponse extends CurlResponse{
  public function error() {
    $error = parent::error();
    if ($error) {
      return $error;
    }

    if (!is_array($this->data)) {
      return sprintf('transloadit: bad response, no json: %s', $this->data);
    }

    if (array_key_exists('error', $this->data)) {
      $error = sprintf('transloadit: %s', $this->data['error']);

      if (array_key_exists('message', $this->data)) {
        $error .= sprintf(': %s', $this->data['message']);
      }

      if (array_key_exists('reason', $this->data)) {
        $error .= sprintf(': %s', $this->data['reason']);
      }

      return $error;
    }

    if (!array_key_exists('ok', $this->data)) {
      return 'transloadit: bad response data, no ok / error key included.';
    }

    return false;
  }
}
?>
