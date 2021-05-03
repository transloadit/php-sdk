<?php

declare(strict_types=1);

namespace Transloadit\Model\Resource\Contracts;

use Symfony\Component\Mime\Part\DataPart;

interface ResourceFileInterface extends ResourceInterface
{
    public function addFilePath(string $filePath): self;

    /**
     * @return DataPart[]
     */
    public function getFiles(): array;
}
