<?php

/**
 * Run the API2 contract TUS resume scenario against a devdock API2 server.
 *
 * This example is intentionally checked into the SDK repository: it should read
 * the API/TUS facts from API2's injected scenario JSON, interrupt an upload like
 * an unlucky user would, and resume it through the public SDK method.
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

function resolveValue($valueSpec, $context, $label) {
  if (!is_array($valueSpec)) {
    throw new \RuntimeException("$label value spec must be an object");
  }
  if (array_key_exists('value', $valueSpec)) {
    return $valueSpec['value'];
  }
  $source = $valueSpec['source'] ?? null;
  if (!is_array($source)) {
    throw new \RuntimeException("$label value spec has no literal value or source");
  }
  $root = $source['root'] ?? null;
  if (!is_string($root) || !array_key_exists($root, $context)) {
    throw new \RuntimeException("$label value source root is unavailable");
  }
  $current = $context[$root];
  foreach ($source['path'] ?? [] as $part) {
    if (!is_array($current) || !array_key_exists($part, $current)) {
      throw new \RuntimeException("$label value source cannot read $part");
    }
    $current = $current[$part];
  }
  return $current;
}

function scenarioBytes($upload) {
  $source = $upload['source'] ?? null;
  if (!is_array($source) || ($source['kind'] ?? null) !== 'bytes') {
    throw new \RuntimeException('upload.source.kind must be bytes');
  }
  if (($source['encoding'] ?? null) !== 'utf8') {
    throw new \RuntimeException('upload.source.encoding must be utf8');
  }
  return (string) $source['value'];
}

function uploadMetadata($upload, $context) {
  $metadata = [];
  foreach ($upload['metadata'] ?? [] as $field) {
    $metadata[$field['name']] = (string) resolveValue($field['value'], $context, $field['name']);
  }
  return $metadata;
}

function curlHeaderCollector(&$responseHeaders) {
  return function ($curl, $headerLine) use (&$responseHeaders) {
    $headerParts = explode(':', $headerLine, 2);
    if (count($headerParts) === 2) {
      $responseHeaders[strtolower(trim($headerParts[0]))] = trim($headerParts[1]);
    }
    return strlen($headerLine);
  };
}

function resolveUploadUrl($tusUrl, $location) {
  if (preg_match('#^https?://#i', $location)) {
    return $location;
  }
  $tusUrlParts = parse_url($tusUrl);
  $origin = $tusUrlParts['scheme'] . '://' . $tusUrlParts['host'] . (isset($tusUrlParts['port']) ? ':' . $tusUrlParts['port'] : '');
  if (substr($location, 0, 1) === '/') {
    return $origin . $location;
  }
  $path = $tusUrlParts['path'] ?? '/';
  return $origin . substr($path, 0, strrpos($path, '/') + 1) . $location;
}

/**
 * Create a TUS upload and only send the first chunk, leaving the upload
 * interrupted the way a dropped connection would.
 */
function createInterruptedUpload($tusUrl, $content, $metadata, $stopAfterAcceptedBytes) {
  $metadataParts = [];
  foreach ($metadata as $name => $value) {
    $metadataParts[] = $name . ' ' . base64_encode($value);
  }
  $createResponseHeaders = [];
  $createCurl = curl_init();
  curl_setopt($createCurl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($createCurl, CURLOPT_URL, $tusUrl);
  curl_setopt($createCurl, CURLOPT_POSTFIELDS, '');
  curl_setopt($createCurl, CURLOPT_HTTPHEADER, [
    'Tus-Resumable: 1.0.0',
    'Upload-Length: ' . strlen($content),
    'Upload-Metadata: ' . implode(',', $metadataParts),
  ]);
  curl_setopt($createCurl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($createCurl, CURLOPT_HEADERFUNCTION, curlHeaderCollector($createResponseHeaders));
  curl_exec($createCurl);
  $createCurlError = curl_error($createCurl);
  $createStatus = (int) curl_getinfo($createCurl, CURLINFO_HTTP_CODE);
  curl_close($createCurl);
  if ($createCurlError !== '') {
    throw new \RuntimeException("TUS create request failed: $createCurlError");
  }
  if ($createStatus !== 201) {
    throw new \RuntimeException("TUS create returned HTTP $createStatus, expected 201");
  }
  $location = $createResponseHeaders['location'] ?? '';
  if (!$location) {
    throw new \RuntimeException('TUS create did not return a Location header');
  }
  $uploadUrl = resolveUploadUrl($tusUrl, $location);

  $patchResponseHeaders = [];
  $patchCurl = curl_init();
  curl_setopt($patchCurl, CURLOPT_CUSTOMREQUEST, 'PATCH');
  curl_setopt($patchCurl, CURLOPT_URL, $uploadUrl);
  curl_setopt($patchCurl, CURLOPT_POSTFIELDS, substr($content, 0, $stopAfterAcceptedBytes));
  curl_setopt($patchCurl, CURLOPT_HTTPHEADER, [
    'Tus-Resumable: 1.0.0',
    'Upload-Offset: 0',
    'Content-Type: application/offset+octet-stream',
  ]);
  curl_setopt($patchCurl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($patchCurl, CURLOPT_HEADERFUNCTION, curlHeaderCollector($patchResponseHeaders));
  curl_exec($patchCurl);
  $patchCurlError = curl_error($patchCurl);
  $patchStatus = (int) curl_getinfo($patchCurl, CURLINFO_HTTP_CODE);
  curl_close($patchCurl);
  if ($patchCurlError !== '') {
    throw new \RuntimeException("TUS first chunk request failed: $patchCurlError");
  }
  if ($patchStatus !== 204) {
    throw new \RuntimeException("TUS first chunk returned HTTP $patchStatus, expected 204");
  }
  $acceptedBytes = (int) ($patchResponseHeaders['upload-offset'] ?? -1);
  if ($acceptedBytes !== $stopAfterAcceptedBytes) {
    throw new \RuntimeException(
      "TUS first chunk accepted $acceptedBytes bytes, expected $stopAfterAcceptedBytes"
    );
  }

  return $uploadUrl;
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

$createResponse = $scenario['prepared']['createResponse'] ?? null;
if (!is_array($createResponse)) {
  throw new \RuntimeException('prepared.createResponse must be an object');
}
$upload = $scenario['upload'] ?? null;
if (!is_array($upload)) {
  throw new \RuntimeException('upload must be an object');
}
$resume = $upload['resume'] ?? null;
if (!is_array($resume)) {
  throw new \RuntimeException('upload.resume must be an object');
}

$context = ['createResponse' => $createResponse, 'scenario' => $scenario];
$content = scenarioBytes($upload);
$tusUrl = (string) resolveValue($upload['tusUrl'], $context, 'upload.tusUrl');
$metadata = uploadMetadata($upload, $context);

$firstUploadUrl = createInterruptedUpload(
  $tusUrl,
  $content,
  $metadata,
  $resume['stopAfterAcceptedBytes']
);

// Remember the interrupted upload by fingerprint, like a TUS client URL storage would.
$storedUploads = [$resume['fingerprint'] => $firstUploadUrl];
$previousUploadCount = count($storedUploads);

$completedAssembly = $client->resumeTusUpload(
  $storedUploads[$resume['fingerprint']],
  $content,
  $createResponse['assembly_ssl_url']
);
responseData($completedAssembly, 'resumeTusUpload');

if ($resume['removeFingerprintOnSuccess']) {
  unset($storedUploads[$resume['fingerprint']]);
}
$remainingPreviousUploadCount = count($storedUploads);

writeResult([
  'firstUploadUrl' => $firstUploadUrl,
  'previousUploadCount' => $previousUploadCount,
  'remainingPreviousUploadCount' => $remainingPreviousUploadCount,
  'uploadUrl' => $firstUploadUrl,
]);

echo "PHP Transloadit SDK devdock scenario {$scenario['exampleInput']['scenarioId']} resumed $firstUploadUrl\n";
