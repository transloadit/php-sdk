<?php
$config = require __DIR__ . '/config/common.php';
$transloadit = new transloadit\Transloadit($config);

/*
### 5. Signature Authentication

... is done by the PHP SDK by default and internally. You do not need to worry about this at all. :)
*/

// There is no need to supply a signature field here or anything.
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
