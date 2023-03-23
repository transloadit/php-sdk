<?php

require __DIR__ . '/common/loader.php';
/*
### 5. Create an Assembly with a Template.

This example demonstrates how you can use the SDK to create an <dfn>Assembly</dfn>
with <dfn>Templates</dfn>.

You are expected to create a <dfn>Template</dfn> on your Transloadit account dashboard
and add the <dfn>Template</dfn> ID here.
*/

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->createAssembly([
  'files' => [dirname(__FILE__) . '/fixture/straw-apple.jpg'],
  'params' => [
    'template_id' => 'YOUR_TEMPLATE_ID',
  ],
]);

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';
