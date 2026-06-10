<?php

/**
 * Run the API2 contract Assembly lifecycle scenario against a devdock API2 server.
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

function assemblyResult($data) {
  return [
    'assemblyId' => $data['assembly_id'] ?? $data['assemblyId'] ?? $data['id'] ?? null,
    'assemblySslUrl' => $data['assembly_ssl_url'] ?? $data['assemblySslUrl'] ?? null,
    'assemblyUrl' => $data['assembly_url'] ?? $data['assemblyUrl'] ?? null,
    'ok' => $data['ok'] ?? null,
  ];
}

function listItems($data) {
  if (isset($data['items']) && is_array($data['items'])) {
    return $data['items'];
  }
  if (isset($data['assemblies']) && is_array($data['assemblies'])) {
    return $data['assemblies'];
  }
  throw new \RuntimeException('assembly list response did not contain items or assemblies');
}

function listCount($data) {
  if (isset($data['count']) && is_int($data['count'])) {
    return $data['count'];
  }
  return count(listItems($data));
}

function itemAssemblyId($item) {
  if (!is_array($item)) {
    return null;
  }
  return $item['assembly_id'] ?? $item['assemblyId'] ?? $item['id'] ?? null;
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

$created = responseData(
  $client->createTusAssembly($scenario['assembly']['fileCount']),
  'createTusAssembly'
);
$createdResult = assemblyResult($created);
$assemblyId = $createdResult['assemblyId'];
$assemblyUrl = $createdResult['assemblySslUrl'];
if (!$assemblyId) {
  throw new \RuntimeException('createTusAssembly returned no assembly id');
}
if (!$assemblyUrl) {
  throw new \RuntimeException('createTusAssembly returned no assembly_ssl_url');
}

$cancelOnExit = true;

try {
  $fetched = responseData($client->getAssemblyByUrl($assemblyUrl), 'getAssemblyStatus');
  $listed = responseData(
    $client->listAssemblies([
      'params' => [
        'assembly_id' => $assemblyId,
        'pagesize' => $scenario['list']['pageSize'],
      ],
    ]),
    'listAssemblies'
  );

  $listContainsCreated = false;
  foreach (listItems($listed) as $item) {
    if (itemAssemblyId($item) === $assemblyId) {
      $listContainsCreated = true;
    }
  }

  $cancelled = responseData($client->cancelAssembly($assemblyId), 'cancelAssembly');
  $cancelOnExit = false;

  writeResult([
    'cancelled' => assemblyResult($cancelled),
    'created' => $createdResult,
    'fetched' => assemblyResult($fetched),
    'listContainsCreated' => $listContainsCreated,
    'listCount' => listCount($listed),
  ]);
} finally {
  if ($cancelOnExit) {
    $client->cancelAssembly($assemblyId);
  }
}

echo "PHP Transloadit SDK devdock scenario {$scenario['scenarioId']} canceled Assembly $assemblyId\n";
