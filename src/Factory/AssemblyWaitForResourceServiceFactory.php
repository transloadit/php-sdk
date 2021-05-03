<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transloadit\Model\Contracts\AuthInterface;
use Transloadit\Service\AssemblyResourceService;
use Transloadit\Service\AssemblyWaitForResourceService;
use Transloadit\Service\Contracts\AssemblyResourceServiceInterface;

class AssemblyWaitForResourceServiceFactory
{
    public static function create(
        AuthInterface $auth,
        HttpClientInterface $client = null,
        SerializerInterface $serializer = null
    ): AssemblyResourceServiceInterface {
        $client = $client ?? TransloaditHttpClientFactory::create();
        $serializer = $serializer ?? SerializerFactory::create();

        return new AssemblyWaitForResourceService($auth, $client, $serializer);
    }
}
