<?php

// Never do this!

// To run: source env.sh && php examples/7-disable-ssl-verification.php

require __DIR__ . '/common/loader.php';

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => getenv('MY_TRANSLOADIT_KEY'),
  'secret' => getenv('MY_TRANSLOADIT_SECRET'),
]);

$response = $transloadit->createAssembly([
  'files' => [dirname(__FILE__) . '/fixture/straw-apple.jpg'],
  'curlOptions' => [
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
  ],
  'waitForCompletion' => true,
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
echo '<xmp>';
print_r($response);
echo '</xmp>';
