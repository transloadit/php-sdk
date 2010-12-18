<?php
require_once(dirname(dirname(dirname(__FILE__))).'/lib/Transloadit/Transloadit.php');


if (!@include_once(dirname(__FILE__).'/config.php')) {
  die('Please check example/config/config.php.template for running the examples.');
}
