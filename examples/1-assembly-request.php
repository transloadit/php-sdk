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
  'key'    => 'MY_TRANSLOADIT_KEY',
  'secret' => 'MY_TRANSLOADIT_SECRET',
]);

$response = $transloadit->createAssembly([
  // Use dirname(__FILE__) to get the current directory, then append the relative path to the image
  // You can replace this with an absolute path to any file on your server that PHP can access
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
