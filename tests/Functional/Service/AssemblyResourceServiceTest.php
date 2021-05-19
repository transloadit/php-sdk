<?php

namespace Transloadit\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\{HttpClientInterface, ResponseInterface};
use Transloadit\Enum\Status;
use Transloadit\Factory\AssemblyResourceServiceFactory;
use Transloadit\Model\Auth;
use Transloadit\Model\Parameter;
use DateTime;
use Transloadit\Model\Resource\Assembly;
use Transloadit\Model\Step;

class AssemblyResourceServiceTest extends TestCase
{
    private $auth;

    public function setUp(): void
    {
        $this->auth = new Auth('fake_key', 'fake_secret', new DateTime('2000-10-20 10:10:00'));
    }

    public function testCanCreateAnAssembly()
    {
        $assembly = new Assembly($this->getParameterObject());

        $client = $this->mockClient('tests/Snapshot/simple_params_post_assembly.json');
        AssemblyResourceServiceFactory::create($this->auth, $client)
            ->create($assembly);
    }

    public function testCanCreateAnAssemblyWithAllParameters()
    {
        $parameter = $this
            ->getParameterObject()
            ->setAllowStepsOverride(false)
            ->setFields(['user_width' => 800])
            ->setNotifyUrl('https://fake.local')
            ->setTemplateId('template_fake')
        ;
        $step = new Step();
        $step
            ->setName('thumbed_fake')
            ->setValue(
                [
                    'use' => 'encoded_fake',
                    'robot' => '/video/thumbs/fake',
                    'count' => 100,
                ]
            )
        ;
        $parameter->addStep($step);

        $assembly = new Assembly();
        $assembly->setParameter($parameter);

        $client = $this->mockClient('tests/Snapshot/full_params_post_assembly.json');
        $assembly = AssemblyResourceServiceFactory::create($this->auth, $client)
            ->create($assembly);

        $this->assertEquals('7828446e5acd4aa996dce3455ec914e9', $assembly->getId());
        $this->assertEquals(Status::ASSEMBLY_COMPLETED, $assembly->getStatus());
        $this->assertEquals('https://api2-jenks.transloadit.com/assemblies/fake', $assembly->getSslUrl());
        $this->assertEquals('http://api2.jenks.transloadit.com/assemblies/fake', $assembly->getUrl());
    }

    public function testCanGetAnAssembly()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(file_get_contents('tests/Snapshot/full_get_assembly.json'));

        $client = $this->createMock(MockHttpClient::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('assemblies/7828446e5acd4aa996dce3455ec914e9')
            )
            ->willReturn($response)
        ;

        $assemblyResource = AssemblyResourceServiceFactory::create($this->auth, $client);

        $assembly = $assemblyResource->getById('7828446e5acd4aa996dce3455ec914e9');
        $this->assertEquals('7828446e5acd4aa996dce3455ec914e9', $assembly->getId());
        $this->assertEquals(Status::ASSEMBLY_COMPLETED, $assembly->getStatus());

    }

    public function testCanCancelAnAssembly()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(file_get_contents('tests/Snapshot/full_get_assembly.json'));

        $client = $this->createMock(MockHttpClient::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('DELETE'),
                $this->equalTo('assemblies/7828446e5acd4aa996dce3455ec914e9')
            )
            ->willReturn($response)
        ;

        $assemblyResource = AssemblyResourceServiceFactory::create($this->auth, $client);

        $assembly = $assemblyResource->cancel('7828446e5acd4aa996dce3455ec914e9');
        $this->assertEquals('7828446e5acd4aa996dce3455ec914e9', $assembly->getId());
        $this->assertEquals(Status::ASSEMBLY_COMPLETED, $assembly->getStatus());
    }

    private function mockClient(string $snapshotPath): HttpClientInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(file_get_contents('tests/Snapshot/full_get_assembly.json'));

        $client = $this->createMock(MockHttpClient::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('assemblies'),
                $this->callback(function (array $options) use ($snapshotPath) {
                    [$params] = iterator_to_array($options['body']);

                    $this->assertJsonStringEqualsJsonFile(
                        $snapshotPath,
                        $params
                    );

                    return true;
                })
            )
            ->willReturn($response)
        ;

        return $client;
    }

    private function getParameterObject(): Parameter
    {
        $step1 = new Step(
            'encoded',
            [
                'use' => ':original',
                'robot' => '/video/encode',
                'preset' => 'iphone-high',
            ]
        );

        $step2 = new Step(
            'thumbed',
            [
                'use' => 'encoded',
                'robot' => '/video/thumbs',
                'count' => 8,
            ]
        );

        $parameter = new Parameter([$step1]);

        return $parameter->addStep($step2);
    }
}