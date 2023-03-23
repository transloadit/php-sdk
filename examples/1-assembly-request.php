<?php

require __DIR__ . '/common/loader.php';
/*
### 1. Upload and resize an image from your server

This example demonstrates how you can use the SDK to create an <dfn>Assembly</dfn>
on your server.

It takes a sample image file, uploads it to Transloadit, and starts a
resizing job on it.
*/

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->createAssembly([
  'files' => [dirname(__FILE__) . '/fixture/straw-apple.jpg'],
  'params' => [
    'steps' => [
      'resize' => [
        'robot' => '/image/resize',
        'width' => 200,
        'height' => 100,
      ],
    ],
  ],
]);

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';
