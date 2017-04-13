<?php
require __DIR__ . '/common/loader.php';

/*
### 4. Fetch the assembly status JSON

You can just use the TransloaditRequest class to get the job done easily.
*/
$assemblyId = 'YOUR_ASSEMBLY_ID';

$req = new transloadit\TransloaditRequest();
$req->path = '/assemblies/' . $assemblyId;
$response = $req->execute();

echo '<pre>';
print_r($response);
echo '</pre>';
