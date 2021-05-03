<?php

declare(strict_types=1);

namespace Transloadit\Service;

use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Transloadit\Model\Contracts\AuthInterface;
use Transloadit\Model\Resource\Contracts\ResourceInterface;

abstract class AbstractResourceService
{
    protected $auth;
    protected $client;
    protected $serializer;

    protected const HTTP_METHOD_GET = 'GET';
    protected const HTTP_METHOD_POST = 'POST';
    protected const HTTP_METHOD_UPDATE = 'UPDATE';
    protected const HTTP_METHOD_DELETE = 'DELETE';

    public function __construct(
        AuthInterface $auth,
        HttpClientInterface $client,
        SerializerInterface $serializer
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->auth = $auth;
    }

    protected function serializeResource($resource): string
    {
        return $this->serializer->serialize(
            $resource,
            'json',
            [
                AbstractObjectNormalizer::GROUPS => ['write'],
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]
        );
    }

    protected function deserializeResource(ResponseInterface $response, string $resourceClass): ResourceInterface
    {
        return $this->serializer->deserialize(
            $response->getContent(),
            $resourceClass,
            'json',
            [
                AbstractObjectNormalizer::GROUPS => ['read']
            ]
        );
    }

    protected function generateFormFields(ResourceInterface $resource): array
    {
        $resource->getParameter()->setAuth($this->auth);
        $params = $this->serializeResource($resource->getParameter());
        $signature = $this->auth->generateSignature($params);

        return [
            'signature' => $signature,
            'params' => $params,
        ];
    }

    protected function requestResource(ResourceInterface $resource, string $url, string $method): ResponseInterface
    {
        $formData = new FormDataPart($this->generateFormFields($resource));

        return $this->client->request(
            $method,
            $url,
            [
                'headers' =>  $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable()
            ]
        );
    }
}
