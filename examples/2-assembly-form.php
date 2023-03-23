<?php

require __DIR__ . '/common/loader.php';
/*
### 2. Create a simple end-user upload form

This example shows you how to create a simple Transloadit upload form
that redirects back to your site after the upload is done.

Once the script receives the redirect request, the current status for
this <dfn>Assembly</dfn> is shown using `Transloadit::response()`.

<div class="tip" markdown="1">
  There is no guarantee that the <dfn>Assembly</dfn> has already finished
  executing by the time the `$response` is fetched. You should use
  the `notify_url` parameter for this.
</div>
*/

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

// Check if this request is a Transloadit redirect_url notification.
// If so fetch the response and output the current assembly status:
$response = Transloadit::response();
if ($response) {
  echo '<h1>Assembly Status:</h1>';
  echo '<pre>';
  print_r($response);
  echo '</pre>';
  exit;
}

// This should work on most environments, but you might have to modify
// this for your particular setup.
$redirectUrl = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);

// Setup a simple file upload form that resizes an image to 200x100px
echo $transloadit->createAssemblyForm([
  'params' => [
    'steps' => [
      'resize' => [
        'robot' => '/image/resize',
        'width' => 200,
        'height' => 100,
      ],
    ],
    'redirect_url' => $redirectUrl,
  ],
]);
?>
<h1>Pick an image to resize</h1>
<input name="example_upload" type="file">
<input type="submit" value="Upload">
</form>
