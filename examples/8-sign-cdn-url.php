<?php

require __DIR__ . '/common/loader.php';

use transloadit\Transloadit;

// Validate environment variables
$requiredEnvVars = ['TRANSLOADIT_KEY', 'TRANSLOADIT_SECRET'];
foreach ($requiredEnvVars as $var) {
  if (!getenv($var)) {
    fwrite(STDERR, "Error: {$var} environment variable is required\n");
    exit(1);
  }
}

// Validate CLI arguments
if ($argc !== 3) {
  fwrite(STDERR, "Usage: php " . basename(__FILE__) . " <expires-at-ms> <unsigned-url>\n");
  fwrite(STDERR, "Example: php " . basename(__FILE__) . " 3600000 workspace-name.tlcdn.com/template-name/image.jpg\n");
  exit(1);
}

// Parse the unsigned URL
$expiresAt = $argv[1];
$unsignedUrl = $argv[2];
if (!preg_match('#^(?:https?://)?([^/]+)/([^/]+)(?:/([^/?]*))?#', $unsignedUrl, $matches)) {
  fwrite(STDERR, "Error: Invalid URL format. Expected: domain/template[/input]\n");
  exit(1);
}

// Extract components
$domain = $matches[1];
$workspaceSlug = explode('.', $domain)[0];
$templateSlug = $matches[2];
$inputField = $matches[3] ?? '';

// Initialize Transloadit with environment variables
$transloadit = new Transloadit([
  'key' => getenv('TRANSLOADIT_KEY'),
  'secret' => getenv('TRANSLOADIT_SECRET'),
]);

// Generate signed URL
try {
  $signedUrl = $transloadit->signedSmartCDNUrl(
    $workspaceSlug,
    $templateSlug,
    $inputField,
    [],
    [
      'authKey' => getenv('TRANSLOADIT_KEY'),
      'authSecret' => getenv('TRANSLOADIT_SECRET'),
      'expiresAtMs' => $expiresAt,
    ]
  );
  echo $signedUrl . "\n";
} catch (Exception $e) {
  fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
  exit(1);
}
