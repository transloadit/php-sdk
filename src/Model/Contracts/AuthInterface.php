<?php

declare(strict_types=1);

namespace Transloadit\Model\Contracts;

use DateTimeInterface;

interface AuthInterface
{
    public function getKey(): string;

    public function getExpires(): DateTimeInterface;

    public function getSecret(): string;

    public function generateSignature(string $params): string;
}
