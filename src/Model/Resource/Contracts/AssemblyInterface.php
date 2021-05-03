<?php

declare(strict_types=1);

namespace Transloadit\Model\Resource\Contracts;

interface AssemblyInterface extends ResourceFileInterface
{
    public function getId(): string;

    public function setId(string $id): self;

    public function getUrl(): string;

    public function setUrl(string $url): self;

    public function getSslUrl(): string;

    public function setSslUrl(string $sslUrl): self;

    public function getStatus(): string;

    public function setStatus(string $status): self;
}
