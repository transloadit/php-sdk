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
  /*
  If you set `blocking` to true, this request will hang until
  transloadit has finished resizing the image.

  This is ok to do with small files, but it requires that the
  network connection remains uninterrupted for the whole time.

  So for bigger files you should use our `notify_url` parameter,
  which contacts your server when the processing is done.
  */
  'blocking' => false,
));

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';
