<?php
require_once('./config/common.php');
$transloadit = new Transloadit($exampleConfig);

/*
### 1. Upload and resize an image from your server

This example demonstrates how you can use the sdk to create an assembly
on your server.

It takes a sample image file, uploads it to transloadit, and starts a
resizing job on it.
*/

$response = $transloadit->createAssembly(array(
  'files' => array(dirname(__FILE__).'/fixture/straw-apple.jpg'),
  'params' => array(
    'steps' => array(
      'resize' => array(
        'robot' => '/image/resize',
        'width' => 200,
        'height' => 100,
      )
    )
  ),
));

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';
