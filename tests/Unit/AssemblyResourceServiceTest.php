<?php


namespace Transloadit\Tests\Unit;


use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Transloadit\Factory\AssemblyResourceServiceFactory;
use Transloadit\Model\Contracts\AuthInterface;
use Transloadit\Model\Parameter;
use Transloadit\Model\Resource\Assembly;

class AssemblyResourceServiceTest extends TestCase
{
    public function testSeeIfGetFilesWasCalledInResource()
    {
        $auth = $this->createMock(AuthInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(file_get_contents('tests/Snapshot/full_get_assembly.json'));

        $client = new MockHttpClient($response, 'https://localhost');

        $assembly = $this->createPartialMock(Assembly::class, ['addFilePath', 'getFiles', 'getParameter']);
        $assembly
            ->expects($this->once())
            ->method('getFiles');
        $assembly
            ->method('getParameter')
            ->willReturn($this->createMock(Parameter::class));
        $assembly->addFilePath('tests/Files/transloadit.png');

        AssemblyResourceServiceFactory::create($auth, $client)
            ->create($assembly);
    }

    public function testCheckAddFile()
    {
        $assembly = new Assembly($this->createMock(Parameter::class));
        $assembly->addFilePath('tests/Files/transloadit.png');

        $this->assertCount(1, $assembly->getFiles());
    }
}