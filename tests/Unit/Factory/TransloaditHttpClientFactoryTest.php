<?php

namespace Transloadit\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transloadit\Factory\TransloaditHttpClientFactory;

class TransloaditHttpClientFactoryTest extends TestCase
{
    public function testCanSeeDefaultUrl()
    {
        $this->assertInstanceOf(HttpClientInterface::class, TransloaditHttpClientFactory::create());
    }
}