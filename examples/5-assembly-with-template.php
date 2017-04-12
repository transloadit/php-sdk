<?php
require __DIR__ . '/common/loader.php';
/*
### 5. Create an assembly with a template.

This example demonstrates how you can use the sdk to create an assembly
with templates.

You are expected to create a Template on your Transloadit account dashboard
and add the template id here.
*/

use transloadit\Transloadit;

$transloadit = new Transloadit(array(
  'key'    => 'TRANSLOADIT_KEY',
  'secret' => 'TRANSLOADIT_SECRET',
));

$response = $transloadit->createAssembly(array(
  'files' => array(dirname(__FILE__).'/fixture/straw-apple.jpg'),
  'params' => array(
    'template_id' => 'YOUR_TEMPLATE_ID'
  ),
));

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';
