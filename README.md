# Transloadit PHP SDK

[![Test Actions Status][test_badge]][test_link]
[![Code quality][code_quality_badge]][code_quality_link]
[![coverage][coverage_badge]][coverage_link]
![Packagist PHP Version Support][php_verison_badge]
[![License][licence_badge]][licence_link]

A **PHP** Integration for [Transloadit](https://transloadit.com)'s file uploading and encoding service

## Intro

[Transloadit](https://transloadit.com) is a service that helps you handle file uploads, resize, crop and watermark your images, make GIFs, transcode your videos, extract thumbnails, generate audio waveforms, and so much more. In short, [Transloadit](https://transloadit.com) is the Swiss Army Knife for your files.

This is a **PHP** SDK to make it easy to talk to the [Transloadit](https://transloadit.com) REST API.

## Getting Started

### Installation

```
composer require transloadit/php-sdk
```

### Documentation

Full documentation can be found over on [transloadit.github.io/php-sdk]([documentation_linkl])

### Basic Usage

```php
require_once 'vendor/autoload.php';

use Transloadit\Model\Auth;
use Transloadit\Factory\AssemblyResourceServiceFactory;
use Transloadit\Model\Step;
use Transloadit\Model\Parameter;
use Transloadit\Model\Resource\Assembly;

//This Auth you will use to all resources.
$auth = new Auth('your_key', 'your_secret');

//create a resource instance, this service will be used to consume assembly resource
$assemblyResource = AssemblyResourceServiceFactory::create($auth);

//create a step
$step1 = new Step('resize', [
    'robot' => '/image/resize',
    'width' => 200,
    'height' => 100,
]);

$parameter = new Parameter([$step1]);
// you can add an step this way too $parameter->addStep($step1)

#create a assembly instance
$assembly = new Assembly($parameter);
$assembly->addFilePath('/PATH/TO/FILE.jpg');

#creating a assembly in transloadit api
$assembly = $assemblyResource->create($assembly);

/**
 The $assembly variable will return an assembly object.
 
 class Transloadit\Model\Resource\Assembly#34 (6) {
     private $id =>
     string(32) "your_id"
     private $url =>
     string(78) "http://api2.deoria.transloadit.com/assemblies/your_id"
     private $sslUrl =>
     string(79) "https://api2-deoria.transloadit.com/assemblies/your_id"
     private $status =>
     string(18) "ASSEMBLY_COMPLETED"
     private $signature =>
     ...
 */
```
 
[documentation_link]: https://transloadit.github.io/php-sdk 
[test_badge]: https://github.com/eerison/php-sdk/workflows/Test/badge.svg?branch=4.x
[test_link]: https://github.com/eerison/php-sdk/actions?query=workflow:test+branch:4.x
[code_quality_badge]: https://api.codeclimate.com/v1/badges/69136f708e9083e4153c/maintainability
[code_quality_link]: https://codeclimate.com/github/eerison/php-sdk/maintainability
[coverage_badge]: https://api.codeclimate.com/v1/badges/69136f708e9083e4153c/test_coverage
[coverage_link]: https://codeclimate.com/github/eerison/php-sdk/test_coverage
[php_verison_badge]: https://img.shields.io/packagist/php-v/transloadit/php-sdk
[licence_badge]: https://img.shields.io/badge/License-MIT-green.svg
[licence_link]: https://github.com/transloadit/php-sdk/blob/master/LICENSE