<?php

/**
 * Run the API2 contract Template lifecycle scenario against a devdock API2 server.
 *
 * This example is intentionally checked into the SDK repository: it should read
 * the API facts from API2's injected scenario JSON and exercise public SDK
 * methods as normal user code would.
 */

require __DIR__ . '/../../vendor/autoload.php';

use transloadit\Transloadit;

function requiredEnv($name) {
  $value = getenv($name);
  if (!$value) {
    throw new \RuntimeException("$name must be set");
  }
  return $value;
}

function loadScenario() {
  $scenarioPath = getenv('API2_SDK_EXAMPLE_SCENARIO');
  if (!$scenarioPath) {
    $scenarioPath = __DIR__ . '/api2-scenario.json';
  }
  $contents = file_get_contents($scenarioPath);
  if ($contents === false) {
    throw new \RuntimeException("could not read scenario file $scenarioPath");
  }
  $scenario = json_decode($contents, true);
  if (!is_array($scenario)) {
    throw new \RuntimeException("scenario file $scenarioPath did not contain a JSON object");
  }
  return $scenario;
}

function responseData($response, $operation) {
  if (is_string($response)) {
    throw new \RuntimeException("$operation failed: $response");
  }
  $data = $response->data;
  if (!is_array($data)) {
    throw new \RuntimeException("$operation returned non-JSON data");
  }
  if (!empty($data['error'])) {
    $message = $data['message'] ?? '';
    throw new \RuntimeException("$operation returned {$data['error']}: $message");
  }
  return $data;
}

function requireTemplateId($data, $operation) {
  $templateId = $data['id'] ?? $data['template_id'] ?? null;
  if (!$templateId) {
    throw new \RuntimeException("$operation returned no template id");
  }
  return $templateId;
}

function templateContent($content) {
  if (!is_array($content)) {
    throw new \RuntimeException('template content must be an object');
  }
  $rendered = $content['additionalProperties'] ?? [];
  $rendered['steps'] = $content['steps'];
  return $rendered;
}

function templatePayload($name, $config) {
  return [
    'name' => $name,
    'require_signature_auth' => $config['requireSignatureAuth'] ? 1 : 0,
    'template' => templateContent($config['content']),
  ];
}

function responseFlag($data, $names) {
  foreach ($names as $name) {
    if (!array_key_exists($name, $data)) {
      continue;
    }
    $value = $data[$name];
    if (is_bool($value)) {
      return $value;
    }
    if (is_int($value)) {
      return $value !== 0;
    }
    if (is_string($value)) {
      return in_array(strtolower($value), ['1', 'true', 'yes'], true);
    }
  }
  return false;
}

function templateResult($data) {
  $content = $data['content'] ?? $data['template'] ?? [];
  if (!is_array($content)) {
    throw new \RuntimeException('template response content must be an object');
  }
  return [
    'content' => $content,
    'id' => $data['id'] ?? $data['template_id'] ?? null,
    'name' => $data['name'] ?? $data['template_name'] ?? null,
    'requireSignatureAuth' => responseFlag($data, ['require_signature_auth', 'requireSignatureAuth']),
  ];
}

function listCount($data) {
  if (isset($data['count']) && is_int($data['count'])) {
    return $data['count'];
  }
  if (isset($data['items']) && is_array($data['items'])) {
    return count($data['items']);
  }
  throw new \RuntimeException('template list response did not contain a count or items list');
}

function deletedGetResult($response) {
  if (is_string($response)) {
    return [false, $response];
  }
  $data = $response->data;
  if (!is_array($data)) {
    return [false, ''];
  }
  $errorCode = $data['error'] ?? '';
  if (is_string($errorCode) && $errorCode !== '') {
    return [false, $errorCode];
  }
  $statusCode = $response->curlInfo['http_code'] ?? null;
  return [$statusCode !== null && $statusCode < 400, ''];
}

function writeResult($result) {
  $resultPath = getenv('API2_SDK_EXAMPLE_RESULT');
  if (!$resultPath) {
    return;
  }
  file_put_contents(
    $resultPath,
    json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
  );
}

$scenario = loadScenario();
$endpoint = requiredEnv('TRANSLOADIT_ENDPOINT');
$client = new Transloadit([
  'key' => requiredEnv('TRANSLOADIT_KEY'),
  'secret' => requiredEnv('TRANSLOADIT_SECRET'),
  'endpoint' => $endpoint,
]);

$templateName = $scenario['template']['namePrefix'] . '-' . hrtime(true);
$templateId = null;
$deleteTemplate = true;

try {
  $created = responseData(
    $client->createTemplate([
      'params' => templatePayload($templateName, $scenario['template']),
    ]),
    'createTemplate'
  );
  $templateId = requireTemplateId($created, 'createTemplate');

  $fetched = responseData($client->getTemplate($templateId), 'getTemplate');
  $listed = responseData(
    $client->listTemplates([
      'params' => [
        'pagesize' => $scenario['list']['pageSize'],
      ],
    ]),
    'listTemplates'
  );

  $updatedTemplateName = $templateName . $scenario['update']['nameSuffix'];
  responseData(
    $client->updateTemplate($templateId, [
      'params' => templatePayload($updatedTemplateName, $scenario['update']),
    ]),
    'updateTemplate'
  );
  $updated = responseData($client->getTemplate($templateId), 'getTemplate updated');

  responseData($client->deleteTemplate($templateId), 'deleteTemplate');
  $deleteTemplate = false;

  [$deletedGetSucceeded, $deletedErrorCode] = deletedGetResult($client->getTemplate($templateId));

  writeResult([
    'deletedErrorCode' => $deletedErrorCode,
    'deletedGetSucceeded' => $deletedGetSucceeded,
    'fetched' => templateResult($fetched),
    'listCount' => listCount($listed),
    'templateId' => $templateId,
    'templateName' => $templateName,
    'updated' => templateResult($updated),
    'updatedTemplateName' => $updatedTemplateName,
  ]);
} finally {
  if ($templateId && $deleteTemplate) {
    $client->deleteTemplate($templateId);
  }
}

echo "PHP Transloadit SDK devdock scenario {$scenario['scenarioId']} passed for $endpoint\n";
