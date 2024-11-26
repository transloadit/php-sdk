<?php

require __DIR__ . '/common/loader.php';
/*
### 3. Integrate the jQuery plugin into the previous example

Integrating the jQuery plugin simply means adding a few lines of JavaScript
to the previous example. Check the HTML comments below to see what changed.

Alternatively, check out [Uppy](https://transloadit.com/docs/sdks/uppy/), our next-gen file uploader for the web.
*/

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'MY_TRANSLOADIT_KEY',
  'secret' => 'MY_TRANSLOADIT_SECRET',
]);

$response = Transloadit::response();
if ($response) {
  echo '<h1>Assembly Status:</h1>';
  echo '<pre>';
  print_r($response);
  echo '</pre>';
  exit;
}

$redirectUrl = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);

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
<!--
  Including the jQuery plugin is as simple as adding jQuery and including the
  JS snippet for the plugin. See https://transloadit.com/docs/sdks/jquery-sdk/
-->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
  var tlProtocol = (('https:' === document.location.protocol) ? 'https://' : 'http://');
  document.write(unescape("%3Cscript src='" + tlProtocol + "assets.transloadit.com/js/jquery.transloadit2.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
  $(document).ready(function() {
    // Tell the transloadit plugin to bind itself to our form
    $('form').transloadit();
  });
</script>
<!-- Nothing changed below here -->
<h1>Pick an image to resize</h1>
<form>
  <input name="example_upload" type="file">
  <input type="submit" value="Upload">
</form>
