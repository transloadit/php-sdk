<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransloaditHttpClientFactory
{
    public static function create(
        $baseUri = 'https://api2.transloadit.com',
        array $defaultOptions = [],
        int $maxHostConnections = 6,
        int $maxPendingPushes = 50
    ): HttpClientInterface {
        return HttpClient::createForBaseUri($baseUri, $defaultOptions, $maxHostConnections, $maxPendingPushes);
    }
}
