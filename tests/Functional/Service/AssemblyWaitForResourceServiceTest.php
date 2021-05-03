<?php

namespace Transloadit\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transloadit\Enum\Status;
use Transloadit\Factory\AssemblyWaitForResourceServiceFactory;
use Transloadit\Model\Contracts\AuthInterface;
use Transloadit\Model\Resource\Contracts\AssemblyInterface;
use Transloadit\Service\AssemblyWaitForResourceService;

class AssemblyWaitForResourceServiceTest extends TestCase
{
    /**
     * @dataProvider provider
     * @param string $firstAssemblyStatus
     * @param string $secondAssemblyStatus
     * @param $execution
     */
    public function testCanCreateAnAssembly(string $firstAssemblyStatus, string $secondAssemblyStatus, $execution)
    {
        $auth = $this->createMock(AuthInterface::class);
        $client = $this->createMock(HttpClientInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $assembly1 = $this->createMock(AssemblyInterface::class);
        $assembly1
            ->method('getStatus')
            ->willReturn($firstAssemblyStatus);

        $assembly2 = $this->createMock(AssemblyInterface::class);
        $assembly2
            ->method('getStatus')
            ->willReturn($secondAssemblyStatus);

        $assemblyService = $this
            ->getMockBuilder(AssemblyWaitForResourceService::class)
            ->setConstructorArgs([$auth, $client, $serializer])
            ->onlyMethods(['getById', 'requestResource', 'deserializeResource'])
            ->getMock()
        ;
        $assemblyService
            ->expects($execution)
            ->method('getById')
            ->willReturn($assembly2)
        ;
        $assemblyService
            ->method('deserializeResource')
            ->willReturn($assembly1);

        $assemblyService->create($assembly1);
    }

    public function testFactory()
    {
        $auth = $this->createMock(AuthInterface::class);
        $assemblyService = AssemblyWaitForResourceServiceFactory::create($auth);

        $this->assertInstanceOf(AssemblyWaitForResourceService::class, $assemblyService);
    }

    public function provider()
    {
        yield [Status::ASSEMBLY_EXECUTING, Status::ASSEMBLY_COMPLETED, $this->once()];
        yield [Status::ASSEMBLY_UPLOADING, Status::ASSEMBLY_COMPLETED, $this->once()];
        yield [Status::ASSEMBLY_COMPLETED, Status::ASSEMBLY_COMPLETED, $this->never()];
    }
}
