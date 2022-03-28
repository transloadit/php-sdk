<?php
require __DIR__ . '/common/loader.php';

/*
### 4. Fetch the Assembly Status JSON

You can use the `getAssembly` method to get the <dfn>Assembly</dfn> Status.
*/
$assemblyId = 'YOUR_ASSEMBLY_ID';

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->getAssembly($assemblyId);

echo '<pre>';
print_r($response);
echo '</pre>';
