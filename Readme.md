# Transloadit PHP SDK

## Purpose

This is the official PHP SDK for interacting with the
[transloadit.com](http://transloadit.com/) API.

## Examples

### Creating a simple transloadit upload form:

    <?php
    require_once(dirname(__FILE__).'/php-sdk/lib/Transloadit.php');

    $transloadit = new Transloadit(array(
      'key' => 'your-key',
      'secret' => 'your-secret'
    ));

    echo $transloadit->form(array(
      'params' => array(
        'steps' => array(
          'resize' => array(
            'robot' => '/image/resize',
            'width' => 200,
            'height' => 200,
          ),
          'store' => array(
            'robot' => '/s3/store',
            'key' => 'your-s3-key',
            'secret' => 'your-s3-secret',
            'bucket' => 'your-s3-bucket',
          )
        )
      )
    ));

## API

### new Transloadit($properties = array());

Creates a new Transloadit base class and applies the given $properties array
to the instance.

### $transloadit->key = null;

The auth key of your transloadit account.

### $transloadit->secret = null;

The auth secret of your transloadit account.

### $transloadit->form($options = array())

Creates a new transloadit upload form including the hidden 'params' and
'signature' field. Available $options are:

* `'params'`: The instructions / steps / template for this assembly.
* `'expires'`: The time where the signature expires. Default: `'+2 hours'`.
* `'protocol'`: Can be `'http'` or `'https'`
* `'fields'`: An optional array with additional field value pairs to include as
              hidden fields into the form. This can be useful to send some
              data to transloadit that you'd like to be included in all status
              requests as well as notifications.
* `'url'`: You should generally not overwrite this.
