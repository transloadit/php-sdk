<?php

require __DIR__ . '/common/loader.php';

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => getenv('YOUR_TRANSLOADIT_KEY'),
  'secret' => getenv('YOUR_TRANSLOADIT_SECRET'),
]);

$response = $transloadit->createAssembly([
  'files' => [dirname(__FILE__) . '/fixture/straw-apple.jpg'],
  'curlOptions' => [
    CURLOPT_TIMEOUT_MS => 1,
    // We can't finish in the specified: '1ms' so we expect this example
    // to fail with: $response->curlErrorNumber === 28
    //
    // You can pass any curl option here that your PHP/curl version supports:
    // https://www.php.net/manual/en/function.curl-setopt.php
    // Note that if you are interested in timeouts, perhaps also consider
    // that you can set waitForCompletion to false and use the
    // notify_url feature to get a webhook pingback when the Assembly is done.
  ],
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
print_r([
  'errcode' => $response->curlErrorNumber,
  'errmsg' => $response->curlErrorMessage,
]);
echo '</xmp>';
