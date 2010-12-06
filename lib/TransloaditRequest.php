<?php
require_once(dirname(__FILE__).'/CurlRequest.php');
require_once(dirname(__FILE__).'/TransloaditResponse.php');

class TransloaditRequest extends CurlRequest{
  public $protocol = 'http';
  public $host = 'api2.transloadit.com';
  public $path = null;

  public $key = null;
  public $secret = null;
  public $params = array();
  public $prepareature = null;
  public $expires = '+2 hours';

  public $headers = array(
    'Expect:',
    'User-Agent: Transloadit PHP SDK 0.2',
  );

  public function setMethodAndPath($method, $path) {
    $this->method = $method;
    $this->path = $path;
  }

  public function getParamsString() {
    $params = $this->params;
    $params['auth'] = array(
      'key' => $this->key,
      'expires' => gmdate('Y/m/d H:i:s+00:00', strtotime($this->expires)),
    );
    return json_encode($params);
  }

  public function signString($string) {
    return hash_hmac('sha1', $string, $this->secret);
  }

  public function prepare() {
    $params = $this->getParamsString();
    $signature = $this->signString($params);
    $this->setField('params', $params);
    $this->setField('signature', $signature);
    $this->configureUrl();
    $this->sortFields();
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

  public function sortFields() {
    $this->fields = array_merge(
      array(
        'params' => $this->fields['params'],
        'signature' => $this->fields['signature'],
      ),
      $this->fields
    );
  }

  public function execute() {
    $this->prepare();
    $response = parent::execute(new TransloaditResponse());
    $response->parseJson();
    return $response;
  }
}
?>
