<?php

require __DIR__ . '/common/loader.php';

/*
### 4. Fetch the Assembly Status JSON

You can use the `getAssembly` method to get the <dfn>Assembly</dfn> Status.
*/
$assemblyId = 'MY_ASSEMBLY_ID';

$transloadit = new Transloadit([
  'key'    => 'MY_TRANSLOADIT_KEY',
  'secret' => 'MY_TRANSLOADIT_SECRET',
]);

$response = $transloadit->getAssembly($assemblyId);

echo '<pre>';
print_r($response);
echo '</pre>';
