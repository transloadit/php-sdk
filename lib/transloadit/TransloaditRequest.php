<?php

namespace transloadit;

class TransloaditRequest extends CurlRequest{
  public $key      = null;
  public $secret   = null;

  public $endpoint = 'https://api2.transloadit.com';
  public $path     = null;

  public $waitForCompletion = false;

  public $params   = array();
  public $expires  = '+2 hours';

  public $headers  = array(
    'Expect:',
    'Transloadit-Client: php-sdk:%s',
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

    if (!ini_get('date.timezone')) {
      date_default_timezone_set('Etc/UTC');
    }

    $params['auth'] = $params['auth'] + array(
      'key'     => $this->key,
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
      '%s%s',
      $this->endpoint,
      $this->path
    );
  }

  public function execute($response = null) {
    // note: $response is not used here, only needed to keep PHP strict mode
    // happy.

    $this->prepare();
    $response = parent::execute(new TransloaditResponse());
    $response->parseJson();

    if ($this->path === '/assemblies' && $this->waitForCompletion) {
      return $this->_waitForCompletion($response);
    }
    return $response;
  }

  private function _waitForCompletion($response) {
    $assemblyUrl = $response->data['assembly_ssl_url'];
    $parts = parse_url($assemblyUrl);

    while (true) {
      $req = new TransloaditRequest();
      $req->endpoint = 'https://' . $parts['host'];
      $req->path = $parts['path'];
      $req->curlOptions = $this->curlOptions;
      $response = $req->execute();

      if (isset($response->data['ok'])) {
        if ($response->data['ok'] === 'ASSEMBLY_UPLOADING' || $response->data['ok'] === 'ASSEMBLY_EXECUTING') {
          sleep(1);
          continue;
        }
      }

      // If this is an unknown, erroneous or completed Assembly completion state, return right away.
      return $response;
    }
  }
}
