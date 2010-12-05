<?php
require_once(dirname(__FILE__).'/CurlRequest.php');
require_once(dirname(__FILE__).'/TransloaditResponse.php');

class TransloaditRequest extends CurlRequest{
  public $service = 'http://api2.transloadit.com';

  public $key = null;
  public $secret = null;
  public $params = array();
  public $prepareature = null;
  public $expires = '+2 hours';

  public $headers = array(
    'Expect:',
    'User-Agent: Transloadit PHP SDK 0.1',
  );

  public function init($method, $path) {
    $this->method = $method;
    $this->url = $this->service.$path;
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
  }

  public function execute() {
    $this->prepare();
    $response = parent::execute(new TransloaditResponse());
    $response->parseJson();
    return $response;
  }
}
?>
