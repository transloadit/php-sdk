<?php

/**
 * Run the API2 contract TUS Assembly scenario against a devdock API2 server.
 *
 * This example is intentionally checked into the SDK repository: it should read
 * the API/TUS facts from API2's injected scenario JSON and exercise public SDK
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

function uploadTusAssemblyInput($scenario) {
  $exampleInput = $scenario['exampleInput'] ?? null;
  if (!is_array($exampleInput)) {
    throw new \RuntimeException('exampleInput must be an object');
  }
  $featureInputs = $exampleInput['sdkFeatureInputs'] ?? null;
  if (!is_array($featureInputs)) {
    throw new \RuntimeException('exampleInput.sdkFeatureInputs must be an object');
  }
  $input = $featureInputs['uploadTusAssembly'] ?? null;
  if (!is_array($input)) {
    throw new \RuntimeException('exampleInput.sdkFeatureInputs.uploadTusAssembly must be an object');
  }
  return $input;
}

function uploadConfig($input) {
  $upload = $input['upload'] ?? null;
  if (!is_array($upload)) {
    throw new \RuntimeException('exampleInput.sdkFeatureInputs.uploadTusAssembly.upload must be an object');
  }
  if (!is_string($upload['content'] ?? null)) {
    throw new \RuntimeException('exampleInput.sdkFeatureInputs.uploadTusAssembly.upload.content must be a string');
  }
  return [
    'content' => $upload['content'],
    'fieldname' => $upload['fieldname'],
    'filename' => $upload['filename'],
    'user_meta' => $upload['user_meta'] ?? [],
  ];
}

function writeResult($status, $uploadUrl) {
  $resultPath = getenv('API2_SDK_EXAMPLE_RESULT');
  if (!$resultPath) {
    return;
  }
  file_put_contents(
    $resultPath,
    json_encode([
      'createResponse' => $status,
      'status' => $status,
      'uploadUrl' => $uploadUrl,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
  );
}

$scenario = loadScenario();
$endpoint = requiredEnv('TRANSLOADIT_ENDPOINT');
$client = new Transloadit([
  'key' => requiredEnv('TRANSLOADIT_KEY'),
  'secret' => requiredEnv('TRANSLOADIT_SECRET'),
  'endpoint' => $endpoint,
]);

$input = uploadTusAssemblyInput($scenario);
$upload = uploadConfig($input);
[$completedAssembly, $uploadUrl] = $client->uploadTusAssembly(
  $input['file_count'],
  $upload['content'],
  $upload['fieldname'],
  $upload['filename'],
  $upload['user_meta']
);
$status = responseData($completedAssembly, 'uploadTusAssembly');
writeResult($status, $uploadUrl);

echo "PHP Transloadit SDK devdock scenario {$scenario['exampleInput']['scenarioId']} passed for $endpoint\n";
