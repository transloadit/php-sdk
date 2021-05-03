<?php

declare(strict_types=1);

namespace Transloadit\Service;

use Transloadit\Model\Resource\Assembly;
use Transloadit\Model\Resource\Contracts\AssemblyInterface;
use Transloadit\Model\Resource\Contracts\ResourceInterface;
use Transloadit\Service\Contracts\AssemblyResourceServiceInterface;

/**
 * Class AssemblyService
 * @package Transloadit\Service
 */
class AssemblyResourceService extends AbstractResourceService implements AssemblyResourceServiceInterface
{
    private const URL = 'assemblies';

    /**
     * @param Assembly $assembly
     * @return AssemblyInterface
     */
    public function create(AssemblyInterface $assembly): ResourceInterface
    {
        $response = $this->requestResource($assembly, self::URL, self::HTTP_METHOD_POST);

        return $this->deserializeResource($response, Assembly::class);
    }

    /**
     * @param string $assemblyId
     * @return AssemblyInterface
     */
    public function cancel(string $assemblyId): ResourceInterface
    {
        $url = sprintf('%s/%s', self::URL, $assemblyId);
        $response = $this->client->request(self::HTTP_METHOD_DELETE, $url);

        return $this->deserializeResource($response, Assembly::class);
    }

    /**
     * @param string $assemblyId
     * @return AssemblyInterface
     */
    public function getById(string $assemblyId): ResourceInterface
    {
        $url = sprintf('%s/%s', self::URL, $assemblyId);
        $response = $this->client->request(self::HTTP_METHOD_GET, $url);

        return $this->deserializeResource($response, Assembly::class);
    }

    public function generateFormFields(ResourceInterface $resource): array
    {
        $formFields = parent::generateFormFields($resource);
        $formFields['files'] = $resource->getFiles();

        return $formFields;
    }
}
