<?php
require_once('./config/common.php');
$transloadit = new Transloadit($exampleConfig);
/*
### 3. Integrate the jQuery plugin into the previous example

Integrating the jQuery plugin simply means adding a few lines of JavaScript
to the previous example. Check the HTML comments below to see what changed.
*/

$response = Transloadit::response();
if ($response) {
  echo '<h1>Assembly status:</h1>';
  echo '<pre>';
  print_r($response);
  echo '</pre>';
  exit;
}

$redirectUrl = sprintf(
  'http://%s%s',
  $_SERVER['HTTP_HOST'],
  $_SERVER['REQUEST_URI']
);

echo $transloadit->createAssemblyForm(array(
  'params' => array(
    'steps' => array(
      'resize' => array(
        'robot' => '/image/resize',
        'width' => 200,
        'height' => 100,
      )
    ),
    'redirect_url' => $redirectUrl
  )
));
?>
<!--
Including the jQuery plugin is as simple as adding jQuery and including the
JS snippet for the plugin. See http://transloadit.com/docs/jquery-plugin
-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script type="text/javascript">
var tlProtocol = (('https:' == document.location.protocol) ? 'https://' : 'http://');
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
<input name="example_upload" type="file">
<input type="submit" value="Upload">
</form>
