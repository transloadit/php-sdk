<?php

namespace transloadit;

class Transloadit {
  public $key      = null;
  public $secret   = null;
  public $endpoint = 'https://api2.transloadit.com';

  public function __construct($attributes = []) {
    foreach ($attributes as $key => $val) {
      $this->{$key} = $val;
    }
  }

  public function request($options = [], $execute = true) {
    $options = $options + [
      'key'               => $this->key,
      'secret'            => $this->secret,
      'endpoint'          => $this->endpoint,
      'waitForCompletion' => false,
    ];
    $request = new TransloaditRequest($options);
    return ($execute)
    ? $request->execute()
    : $request;
  }

  public static function response() {
    if (!empty($_POST['transloadit'])) {
      $json = $_POST['transloadit'];
      if (ini_get('magic_quotes_gpc') === '1') {
        $json = stripslashes($json);
      }

      $response = new TransloaditResponse();
      $response->data = json_decode($json, true);
      return $response;
    }

    if (!empty($_GET['assembly_url'])) {
      $request = new TransloaditRequest([
        'url' => $_GET['assembly_url'],
      ]);
      return $request->execute();
    }
    return false;
  }

  public function createAssemblyForm($options = []) {
    $out = [];

    $customFormAttributes = [];
    if (array_key_exists('attributes', $options)) {
      $customFormAttributes = $options['attributes'];
      unset($options['attributes']);
    }

    $assembly = $this->request($options + [
      'method' => 'POST',
      'path'   => '/assemblies',
    ], false);
    $assembly->prepare();

    $formAttributes = [
      'action'  => $assembly->url,
      'method'  => $assembly->method,
      'enctype' => 'multipart/form-data',
    ] + $customFormAttributes;

    $formAttributeList = [];
    foreach ($formAttributes as $key => $val) {
      $formAttributeList[] = sprintf('%s="%s"', $key, htmlentities($val));
    }

    $out[] = '<form ' . join(' ', $formAttributeList) . '>';

    foreach ($assembly->fields as $field => $val) {
      $out[] = sprintf(
        '<input type="%s" name="%s" value="%s">',
        'hidden',
        $field,
        htmlentities($val)
      );
    }

    return join("\n", $out);
  }

  public function createAssembly($options) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => '/assemblies',
    ]);
  }

  // Leave this in for BC.
  public function getAssembly($assembly_id) {
    $response = $this->request([
      'method' => 'GET',
      'path'   => '/assemblies/' . $assembly_id,
    ], true);

    return $response;
  }

  public function deleteAssembly($assembly_id) {
    return $this->cancelAssembly($assembly_id);
  }

  public function cancelAssembly($assembly_id) {
    // Look up the host for this assembly
    $response = $this->request([
      'method' => 'GET',
      'path'   => '/assemblies/' . $assembly_id,
    ], true);

    $error = $response->error();
    if ($error) {
      return $error;
    }

    $url = parse_url($response->data['assembly_url']);

    $response = $this->request([
      'method' => 'DELETE',
      'path'   => $url['path'],
      'host'   => $url['host'],
    ]);

    $error = $response->error();
    if ($error) {
      return $error;
    } else {
      return $response;
    }
  }

  /**
   * Generates a signed URL for Transloadit's Smart CDN
   * https://transloadit.com/services/content-delivery/
   *
   * @param string $workspaceSlug The workspace slug
   * @param string $templateSlug The template slug
   * @param string $inputField The input field (optional)
   * @param array $params Additional parameters (optional)
   * @param array $signProps Array containing authKey, authSecret, and expireAtMs
   * @return string The signed URL
   */
  public function signedSmartCDNUrl(
      string $workspaceSlug,
      string $templateSlug,
      string $inputField = '',
      array $params = [],
      array $signProps = []
  ): string {
    // Validate required fields
    if (!$workspaceSlug) {
      throw new \InvalidArgumentException('workspace is required');
    }
    if (!$templateSlug) {
      throw new \InvalidArgumentException('template is required');
    }
    if ($inputField === null) {
      throw new \InvalidArgumentException('input must be a string');
    }

    // Add auth parameters
    $queryParams = [];

    // Process params to match Node.js behavior
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $val) {
          if ($val !== null && $val !== '') {
            $queryParams[$key][] = $val;
          }
        }
      } elseif ($value !== null && $value !== '') {
        $queryParams[$key] = $value;
      }
    }

    $queryParams['auth_key'] = $signProps['authKey'] ?? $this->key;
    $queryParams['exp'] = (string)($signProps['expireAtMs'] ?? (time() * 1000 + 3600000)); // Default 1 hour

    // Sort parameters alphabetically
    ksort($queryParams);

    // Build query string manually to match Node.js behavior
    $queryParts = [];
    foreach ($queryParams as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $val) {
          $queryParts[] = rawurlencode($key) . '=' . rawurlencode($val);
        }
      } else {
        $queryParts[] = rawurlencode($key) . '=' . rawurlencode($value);
      }
    }
    $queryString = implode('&', $queryParts);

    // Build the string to sign
    $stringToSign = sprintf(
      '%s/%s/%s?%s',
      rawurlencode($workspaceSlug),
      rawurlencode($templateSlug),
      rawurlencode($inputField),
      $queryString
    );

    // Generate signature
    $signature = hash_hmac('sha256', $stringToSign, $signProps['authSecret'] ?? $this->secret);

    // Add signature to query string
    $finalQueryString = $queryString . '&sig=' . rawurlencode('sha256:' . $signature);

    // Build final URL
    return sprintf(
      'https://%s.tlcdn.com/%s/%s?%s',
      rawurlencode($workspaceSlug),
      rawurlencode($templateSlug),
      rawurlencode($inputField),
      $finalQueryString
    );
  }
}
