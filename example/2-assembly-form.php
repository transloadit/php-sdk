<?php
require_once('./config/common.php');
$transloadit = new Transloadit($exampleConfig);
/*
### 2. Create a simple end-user upload form

This example shows you how to create a simple transloadit upload form
that redirects back to your site after the upload is done.

Once the script receives the redirect request, the current status for
this assembly is shown using Transloadit::response().

Note: There is no guarantee that the assembly has already finished
executing by the time the `$response` is fetched. You should use
the `notify_url` parameter for this.
*/

// Check if this request is a transloadit redirect_url notification.
// If so fetch the response and output the current assembly status:
$response = Transloadit::response();
if ($response) {
  echo '<h1>Assembly status:</h1>';
  echo '<pre>';
  print_r($response);
  echo '</pre>';
  exit;
}

// This should work on most environments, but you might have to modify
// this for your particular setup.
$redirectUrl = sprintf(
  'http://%s%s',
  $_SERVER['HTTP_HOST'],
  $_SERVER['REQUEST_URI']
);

// Setup a simple file upload form that resizes an image to 200x100px
echo $transloadit->createAssemblyForm(array(
  'params' => array(
    'steps' => array(
      'resize' => array(
        'robot' => '/image/resize',
        'width' => 200,
        'height' => 100,
      )
    ),
     // See note about this parameter from example 1
    'blocking' => false,
    'redirect_url' => $redirectUrl
  )
));
?>
<h1>Pick an image to resize</h1>
<input name="example_upload" type="file">
<input type="submit" value="Upload">
</form>
