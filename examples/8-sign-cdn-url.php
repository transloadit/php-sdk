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
if ($argc !== 5) {
  fwrite(STDERR, "Usage: php " . basename(__FILE__) . " <expires-at-ms> <workspace> <template> <input>\n");
  fwrite(STDERR, "Example: php " . basename(__FILE__) . " 1732550672867 my-app test-smart-cdn inputs/image.jpg\n");
  exit(1);
}

// Parse arguments
$expiresAt = $argv[1];
$workspaceSlug = $argv[2];
$templateSlug = $argv[3];
$inputField = $argv[4];

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
    ['expireAtMs' => $expiresAt]
  );
  echo $signedUrl . "\n";
} catch (Exception $e) {
  fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
  exit(1);
}
