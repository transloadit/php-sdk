<?php

namespace transloadit;

class Transloadit {
  public $key      = null;
  public $secret   = null;
  public $signatureAlgorithm = 'sha384';
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
      'signatureAlgorithm' => $this->signatureAlgorithm,
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

  /**
   * Generates a signed URL for Transloadit's Smart CDN
   * https://transloadit.com/services/content-delivery/
   *
   * @param string $workspaceSlug The workspace slug
   * @param string $templateSlug The template slug
   * @param string $inputField The input field (optional)
   * @param array $params Additional parameters (optional)
   * @param int $expireAtMs Number of milliseconds since epoch at which the URL expires
   * @return string The signed URL
   */
  public function signedSmartCDNUrl(
      string $workspaceSlug,
      string $templateSlug,
      string $inputField = '',
      array $params = [],
      ?int $expireAtMs = null
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
          if ($val !== null) {
            $queryParams[$key][] = $val;
          }
        }
      } elseif ($value !== null) {
        $queryParams[$key] = $value;
      }
    }

    $queryParams['auth_key'] = $this->key;
    $queryParams['exp'] = (string)($expireAtMs ?? (time() * 1000 + 3600000)); // Default 1 hour

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
    $signature = hash_hmac('sha256', $stringToSign, $this->secret);

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

  // <api2-generated-endpoints>
  // This block is generated from Transloadit API2 contracts. If it looks wrong,
  // please report the issue instead of editing this block by hand; the source fix
  // belongs in the contract generator so all SDKs stay in sync.

  /**
   * Create a new Assembly.
   *
   * @param array $options TransloaditRequest options such as 'params', 'fields', or 'files'.
   * @return TransloaditResponse
   */
  public function createAssembly($options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => '/assemblies',
    ]);
  }

  /**
   * Create Assembly With Id.
   *
   * @param string $assembly_id
   * @param array $options TransloaditRequest options such as 'params', 'fields', or 'files'.
   * @return TransloaditResponse
   */
  public function createAssemblyWithId($assembly_id, $options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => sprintf('/assemblies/%s', rawurlencode($assembly_id)),
    ]);
  }

  /**
   * Retrieve list of Assemblies.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function listAssemblies($options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => '/assemblies',
    ]);
  }

  /**
   * Retrieve an Assembly Status.
   *
   * @param string $assembly_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getAssembly($assembly_id, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/assemblies/%s', rawurlencode($assembly_id)),
    ]);
  }

  /**
   * Cancel a running Assembly.
   *
   * @param string $assembly_id
   * @return TransloaditResponse|string The response, or an error string on failure.
   */
  public function cancelAssembly($assembly_id) {
    // Look up the host for this assembly
    $response = $this->request([
      'method' => 'GET',
      'path'   => sprintf('/assemblies/%s', rawurlencode($assembly_id)),
    ]);

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
    }

    return $response;
  }

  /**
   * Cancel a running Assembly.
   *
   * Kept for backward compatibility: delegates to cancelAssembly().
   *
   * @param string $assembly_id
   * @return TransloaditResponse|string The response, or an error string on failure.
   */
  public function deleteAssembly($assembly_id) {
    return $this->cancelAssembly($assembly_id);
  }

  /**
   * Replay an Assembly.
   *
   * @param string $assembly_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function replayAssembly($assembly_id, $options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => sprintf('/assemblies/%s/replay', rawurlencode($assembly_id)),
    ]);
  }

  /**
   * Replay Assembly Notification.
   *
   * @param string $assembly_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function replayAssemblyNotification($assembly_id, $options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => sprintf('/assembly_notifications/%s/replay', rawurlencode($assembly_id)),
    ]);
  }

  /**
   * List Assembly Notifications.
   *
   * @param string $assembly_id
   * @return TransloaditResponse
   */
  public function listAssemblyNotifications($assembly_id) {
    return $this->request([
      'method' => 'GET',
      'path'   => sprintf('/assembly_notifications/%s', rawurlencode($assembly_id)),
    ]);
  }

  /**
   * Retrieve a month’s bill.
   *
   * @param int $month
   * @param int $year
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getBill($month, $year, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/bill/%d-%02d', $year, $month),
    ]);
  }

  /**
   * Retrieve list of Templates.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function listTemplates($options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => '/templates',
    ]);
  }

  /**
   * Create a new Template.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function createTemplate($options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => '/templates',
    ]);
  }

  /**
   * Retrieve a Template.
   *
   * @param string $template_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getTemplate($template_id, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/templates/%s', rawurlencode($template_id)),
    ]);
  }

  /**
   * Get Builtin Template.
   *
   * @param string $builtin_template_slug
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getBuiltinTemplate($builtin_template_slug, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/templates/builtin/%s', rawurlencode($builtin_template_slug)),
    ]);
  }

  /**
   * Get Template Full.
   *
   * @param string $template_id_or_name
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getTemplateFull($template_id_or_name, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/templates/%s/full', rawurlencode($template_id_or_name)),
    ]);
  }

  /**
   * Get Builtin Template Full.
   *
   * @param string $builtin_template_slug
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getBuiltinTemplateFull($builtin_template_slug, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/templates/builtin/%s/full', rawurlencode($builtin_template_slug)),
    ]);
  }

  /**
   * Edit a Template.
   *
   * @param string $template_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function updateTemplate($template_id, $options = []) {
    return $this->request($options + [
      'method' => 'PUT',
      'path'   => sprintf('/templates/%s', rawurlencode($template_id)),
    ]);
  }

  /**
   * Delete a Template.
   *
   * @param string $template_id
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function deleteTemplate($template_id, $options = []) {
    return $this->request($options + [
      'method' => 'DELETE',
      'path'   => sprintf('/templates/%s', rawurlencode($template_id)),
    ]);
  }

  /**
   * Retrieve currently used priority job slots.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function listPriorityJobSlots($options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => '/queues/job_slots',
    ]);
  }

  /**
   * Retrieve list of Template Credentials.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function listTemplateCredentials($options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => '/template_credentials',
    ]);
  }

  /**
   * List Template Credential Types.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function listTemplateCredentialTypes($options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => '/template_credentials/types',
    ]);
  }

  /**
   * Validate Template Credential OAuth On Create.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function validateTemplateCredentialOauthOnCreate($options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => '/template_credentials/validateOauthOnCreate',
    ]);
  }

  /**
   * Create a new Template Credential.
   *
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function createTemplateCredentials($options = []) {
    return $this->request($options + [
      'method' => 'POST',
      'path'   => '/template_credentials',
    ]);
  }

  /**
   * Retrieve a Template Credential.
   *
   * @param string $identifier
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function getTemplateCredentials($identifier, $options = []) {
    return $this->request($options + [
      'method' => 'GET',
      'path'   => sprintf('/template_credentials/%s', rawurlencode($identifier)),
    ]);
  }

  /**
   * Delete a Template Credential.
   *
   * @param string $identifier
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function deleteTemplateCredentials($identifier, $options = []) {
    return $this->request($options + [
      'method' => 'DELETE',
      'path'   => sprintf('/template_credentials/%s', rawurlencode($identifier)),
    ]);
  }

  /**
   * Edit a Template Credential.
   *
   * @param string $identifier
   * @param array $options TransloaditRequest options such as 'params'.
   * @return TransloaditResponse
   */
  public function updateTemplateCredentials($identifier, $options = []) {
    return $this->request($options + [
      'method' => 'PUT',
      'path'   => sprintf('/template_credentials/%s', rawurlencode($identifier)),
    ]);
  }

  // </api2-generated-endpoints>
}
