<?php

namespace transloadit;

/**
 * Class CurlResponse
 * @package transloadit
 *
 * @deprecated Tis deprecated since version 3.1.0, please use Transloadit\Service\AssemblyResourceService
 */
class CurlResponse{
  public $data = null;

  public $curlInfo = null;
  public $curlErrorNumber = null;
  public $curlErrorMessage = null;

  // Apply all passed attributes to the instance
  public function __construct($attributes = array()) {
    @trigger_error(
        sprintf(
            'This class %s is deprecated since version 3.1.0, please use %s',
            CurlResponse::class,
            'Transloadit\Service\AssemblyResourceService'
        ),
        E_USER_DEPRECATED
    );

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
?>
