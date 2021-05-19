<?php

declare(strict_types=1);

namespace Transloadit\Service\Contracts;

use Transloadit\Model\Resource\Contracts\AssemblyInterface;
use Transloadit\Model\Resource\Contracts\ResourceInterface;

interface AssemblyResourceServiceInterface
{
    public function create(AssemblyInterface $assembly): ResourceInterface;

    public function cancel(string $assemblyId): ResourceInterface;

    public function getById(string $assemblyId): ResourceInterface;
}
