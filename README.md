# Transloadit PHP SDK

[![Test Actions Status][test_badge]][test_link]
[![Code coverage][codecov_badge]][codecov_link]
![Packagist PHP Version Support][php_verison_badge]
[![License][licence_badge]][licence_link]

## Introduction

The Transloadit PHP SDK provides a simple and efficient way to interact with Transloadit's file processing service in your PHP applications. With this SDK, you can easily:

- Create and manage file upload assemblies
- Use pre-defined templates for common file processing tasks
- Handle notifications and retrieve assembly statuses
- Integrate Transloadit's powerful file processing capabilities into your PHP projects

This SDK simplifies the process of working with Transloadit's REST API, allowing you to focus on building great applications without worrying about the complexities of file processing.

## Install

```
composer require transloadit/php-sdk
```

Keep your Transloadit account's Auth Key & Secret nearby. You can check
the [API credentials](https://transloadit.com/accounts/credentials) page for
these values.

## Usage

<!-- This section is generated by: make docs -->

### 1. Upload and resize an image from your server

This example demonstrates how you can use the SDK to create an <dfn>Assembly</dfn>
on your server.

It takes a sample image file, uploads it to Transloadit, and starts a
resizing job on it.

```php
<?php
require 'vendor/autoload.php';

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->createAssembly([
  'files' => ['/PATH/TO/FILE.jpg'],
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

```

### 2. Create a simple end-user upload form

This example shows you how to create a simple Transloadit upload form
that redirects back to your site after the upload is done.

Once the script receives the redirect request, the current status for
this <dfn>Assembly</dfn> is shown using `Transloadit::response()`.

<div class="alert alert-note">
  <strong>Note:</strong> There is no guarantee that the <dfn>Assembly</dfn> has already finished
  executing by the time the <code>$response</code> is fetched. You should use
  the <code>notify_url</code> parameter for this.
</div>

```php
<?php
require 'vendor/autoload.php';

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

```

### 3. Use Uppy for file uploads

We recommend using [Uppy](https://transloadit.com/docs/sdks/uppy/), our next-gen file uploader for the web, instead of the jQuery SDK. Uppy provides a more modern, flexible, and feature-rich solution for handling file uploads with Transloadit.

To integrate Uppy with your PHP backend:

1. Include Uppy in your HTML:

```html
<link href="https://releases.transloadit.com/uppy/v3.3.1/uppy.min.css" rel="stylesheet">
<script src="https://releases.transloadit.com/uppy/v3.3.1/uppy.min.js"></script>
```

2. Initialize Uppy with Transloadit plugin:

```html
<div id="uppy"></div>

<script>
  const uppy = new Uppy.Core()
    .use(Uppy.Dashboard, {
      inline: true,
      target: '#uppy'
    })
    .use(Uppy.Transloadit, {
      params: {
        auth: { key: 'YOUR_TRANSLOADIT_KEY' },
        template_id: 'YOUR_TEMPLATE_ID',
        notify_url: 'https://your-site.com/transloadit_notify.php'
      }
    })

  uppy.on('complete', (result) => {
    console.log('Upload complete! We've uploaded these files:', result.successful)
  })
</script>
```

3. Handle the assembly status on your PHP backend:

Create a new file named `transloadit_notify.php` in your project:

```php
<?php
require 'vendor/autoload.php';

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = Transloadit::response();
if ($response) {
  // Process the assembly result
  $assemblyId = $response->data['assembly_id'];
  $assemblyStatus = $response->data['ok'];
  
  // You can store the assembly information in your database
  // or perform any other necessary actions here
  
  // Log the response for debugging
  error_log('Transloadit Assembly Completed: ' . $assemblyId);
  error_log('Assembly Status: ' . ($assemblyStatus ? 'Success' : 'Failed'));
  
  // Optionally, you can write the response to a file
  file_put_contents('transloadit_response_' . $assemblyId . '.json', json_encode($response->data));
  
  // Send a 200 OK response to Transloadit
  http_response_code(200);
  echo 'OK';
} else {
  // If it's not a Transloadit notification, return a 400 Bad Request
  http_response_code(400);
  echo 'Bad Request';
}
?>
```

Make sure to replace `'https://your-site.com/transloadit_notify.php'` with the actual URL where you'll host this PHP script.

For more detailed information on using Uppy with Transloadit, please refer to our [Uppy documentation](https://transloadit.com/docs/sdks/uppy/).

### 4. Fetch the Assembly Status JSON

You can use the `getAssembly` method to get the <dfn>Assembly</dfn> Status.

```php
<?php
require 'vendor/autoload.php';
$assemblyId = 'YOUR_ASSEMBLY_ID';

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->getAssembly($assemblyId);

echo '<pre>';
print_r($response);
echo '</pre>';

```

### 5. Create an Assembly with a Template.

This example demonstrates how you can use the SDK to create an <dfn>Assembly</dfn>
with <dfn>Templates</dfn>.

You are expected to create a <dfn>Template</dfn> on your Transloadit account dashboard
and add the <dfn>Template</dfn> ID here.

```php
<?php
require 'vendor/autoload.php';

use transloadit\Transloadit;

$transloadit = new Transloadit([
  'key'    => 'YOUR_TRANSLOADIT_KEY',
  'secret' => 'YOUR_TRANSLOADIT_SECRET',
]);

$response = $transloadit->createAssembly([
  'files' => ['/PATH/TO/FILE.jpg'],
  'params' => [
    'template_id' => 'YOUR_TEMPLATE_ID',
  ],
]);

// Show the results of the assembly we spawned
echo '<pre>';
print_r($response);
echo '</pre>';

```

<!-- End of generated doc section -->

### Signature Auth

<dfn>Signature Authentication</dfn> is done by the PHP SDK by default internally so you do not need to worry about this :)

## Example

For fully working examples take a look at [`examples/`](https://github.com/transloadit/php-sdk/tree/HEAD/examples).

## API

### $Transloadit = new Transloadit($properties = []);

Creates a new Transloadit instance and applies the given $properties.

#### $Transloadit->key = null;

The auth key of your Transloadit account.

#### $Transloadit->secret = null;

The auth secret of your Transloadit account.

#### $Transloadit->request($options = [], $execute = true);

Creates a new `TransloaditRequest` using the `$Transloadit->key` and
`$Transloadit->secret` properties.

If `$execute` is set to `true`, `$TransloaditRequest->execute()` will be
called and used as the return value.

Otherwise the new `TransloaditRequest` instance is being returned.

#### $Transloadit->createAssemblyForm($options = []);

Creates a new Transloadit assembly form including the hidden 'params' and
'signature' fields. A closing form tag is not included.

`$options` is an array of `TransloaditRequest` properties to be used.
For example: `"params"`, `"expires"`, `"endpoint"`, etc..

In addition to that, you can also pass an `"attributes"` key, which allows
you to set custom form attributes. For example:

```php
$Transloadit->createAssemblyForm(array(
  'attributes' => array(
    'id'    => 'my_great_upload_form',
    'class' => 'transloadit_form',
  ),
));
```

#### $Transloadit->createAssembly($options);

Sends a new assembly request to Transloadit. This is the preferred way of
uploading files from your server.

`$options` is an array of `TransloaditRequest` properties to be used with the exception that you can
also use the `waitForCompletion` option here:

`waitForCompletion` is a boolean (default is false) to indicate whether you want to wait for the
Assembly to finish with all encoding results present before the callback is called. If
waitForCompletion is true, this SDK will poll for status updates and return when all encoding work
is done.

Check example #1 above for more information.

#### $Transloadit->getAssembly($assemblyId);

Retrieves the Assembly status json for a given Assembly ID.

#### $Transloadit->cancelAssembly($assemblyId);

Cancels an assembly that is currently executing and prevents any further encodings costing money.

This will result in `ASSEMBLY_NOT_FOUND` errors if invoked on assemblies that are not currently
executing (anymore).

#### Transloadit::response()

This static method is used to parse the notifications Transloadit sends to
your server.

There are two kinds of notifications this method handles:

- When using the `redirect_url` parameter, and Transloadit redirects
  back to your site, a `$_GET['assembly_url']` query parameter gets added.
  This method detects the presence of this parameter and fetches the current
  assembly status from that url and returns it as a `TransloaditResponse`.
- When using the `notify_url` parameter, Transloadit sends a
  `$_POST['transloadit']` parameter. This method detects this, and parses
  the notification JSON into a `TransloaditResponse` object for you.

If the current request does not seem to be invoked by Transloadit, this
method returns `false`.

### $TransloaditRequest = new TransloaditRequest($properties = []);

Creates a new TransloaditRequest instance and applies the given $properties.

#### $TransloaditRequest->key = null;

The auth key of your Transloadit account.

#### $TransloaditRequest->secret = null;

The auth secret of your Transloadit account.

#### $TransloaditRequest->method = 'GET';

Inherited from `CurlRequest`. Can be used to set the type of request to be
made.

#### $TransloaditRequest->curlOptions = [];

Inherited from `CurlRequest`. Can be used to tweak cURL behavior using [any cURL option that your PHP/cURL version supports](https://www.php.net/manual/en/function.curl-setopt.php).

Here is an [example](examples/6-assembly-with-timeout.php) that illustrates
using this option to change the timeout of a request (drastically, to `1ms`, just to prove you can make the SDK abort after a time of your choosing).

The default timeouts and options depend on the cURL version on your system and can be verified by checking `phpinfo()` and the [curl_setopt](https://www.php.net/manual/en/function.curl-setopt.php) documentation.

#### $TransloaditRequest->endpoint = 'https://api2.transloadit.com';

The endpoint to send this request to.

#### $TransloaditRequest->path = null;

The url path to request.

#### $TransloaditRequest->url = null;

Inherited from `CurlRequest`. Lets you overwrite the above endpoint / path
properties with a fully custom url alltogether.

#### $TransloaditRequest->fields = [];

A list of additional fields to send along with your request. Transloadit
will include those in all assembly related notifications.

#### $TransloaditRequest->files = [];

An array of paths to local files you would like to upload. For example:

```php
$TransloaditRequest->files = array('/my/file.jpg');
```

or

```php
$TransloaditRequest->files = array('my_upload' => '/my/file.jpg');
```

The first example would automatically give your file a field name of
`'file_1'` when executing the request.

#### $TransloaditRequest->params = [];

An array representing the JSON params to be send to Transloadit. You
do not have to include an `'auth'` key here, as this class handles that
for you as part of `$TransloaditRequest->prepare()`.

#### $TransloaditRequest->expires = '+2 hours';

If you have configured a '`$TransloaditRequest->secret`', this class will
automatically sign your request. The expires property lets you configure
the duration for which the signature is valid.

#### $TransloaditRequest->headers = [];

Lets you send additional headers along with your request. You should not
have to change this property.

#### $TransloaditRequest->execute()

Sends this request to Transloadit and returns a `TransloaditResponse`
instance.

### $TransloaditResponse = new TransloaditResponse($properties = []);

Creates a new TransloaditResponse instance and applies the given $properties.

#### $TransloaditResponse->data = null;

Inherited from `CurlResponse`. Contains an array of the parsed JSON
response from Transloadit.

You should generally only access this property after having checked for
errors using `$TransloaditResponse->error()`.

#### $TransloaditResponse->error();

Returns `false` or a string containing an explanation of what went wrong.

All of the following will cause an error string to be returned:

- Network issues of any kind
- The Transloadit response JSON contains an `{"error": "..."}` key
- A malformed response was received

**_Note_**: You will need to set waitForCompletion = True in the $Transloadit->createAssembly($options) function call.

## Contributing

Feel free to fork this project. We will happily merge bug fixes or other small
improvements. For bigger changes you should probably get in touch with us
before you start to avoid not seeing them merged.

## Versioning

This project implements the Semantic Versioning guidelines.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

- Breaking backward compatibility bumps the major (and resets the minor and patch)
- New additions without breaking backward compatibility bumps the minor (and resets the patch)
- Bug fixes and misc changes bumps the patch

For more information on SemVer, please visit http://semver.org/.

## Releasing a new version

```bash
# 1. update CHANGELOG.md
# 2. update composer.json
# 3. commit all your work
source env.sh && VERSION=3.1.0 ./release.sh
```

## License

[MIT Licensed](LICENSE)

[test_badge]: https://github.com/transloadit/php-sdk/actions/workflows/tests.yml/badge.svg
[test_link]: https://github.com/transloadit/php-sdk/actions/workflows/tests.yml
[codecov_badge]: https://codecov.io/gh/transloadit/php-sdk/branch/main/graph/badge.svg
[codecov_link]: https://codecov.io/gh/transloadit/php-sdk
[php_verison_badge]: https://img.shields.io/packagist/php-v/transloadit/php-sdk
[licence_badge]: https://img.shields.io/badge/License-MIT-green.svg
[licence_link]: https://github.com/transloadit/php-sdk/blob/main/LICENSE
