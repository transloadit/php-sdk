<?php
require_once(dirname(__FILE__).'/CurlRequest.php');
require_once(dirname(__FILE__).'/TransloaditResponse.php');

class TransloaditRequest extends CurlRequest{
  public $key = null;
  public $secret = null;

  public $protocol = 'http';
  public $host = 'api2.transloadit.com';
  public $path = null;

  public $params = array();
  public $expires = '+2 hours';

  public $headers = array(
    'Expect:',
    'User-Agent: Transloadit PHP SDK 0.9.1',
  );

  public function setMethodAndPath($method, $path) {
    $this->method = $method;
    $this->path = $path;
  }

  public function getParamsString() {
    $params = $this->params;
    if (!isset($params['auth'])) {
      $params['auth'] = array();
    }

    $params['auth'] = $this->params['auth'] + array(
      'key' => $this->key,
      'expires' => gmdate('Y/m/d H:i:s+00:00', strtotime($this->expires)),
    );
    return json_encode($params);
  }

  public function signString($string) {
    if (empty($this->secret)) {
      return null;
    }

    return hash_hmac('sha1', $string, $this->secret);
  }

  public function prepare() {
    $params = $this->getParamsString();
    $this->fields['params'] = $params;

    $signature = $this->signString($params);
    if ($signature) {
      $this->fields['signature'] = $signature;
    }

    $this->configureUrl();
  }

  public function configureUrl() {
    if (!empty($this->url)) {
      return;
    }

    $this->url = sprintf(
      '%s://%s%s',
      $this->protocol,
      $this->host,
      $this->path
    );
  }

  public function execute($response = null) {
    // note: $response is not used here, only needed to keep PHP strict mode
    // happy.

    $this->prepare();
    $response = parent::execute(new TransloaditResponse());
    $response->parseJson();
    return $response;
  }
}
?>
