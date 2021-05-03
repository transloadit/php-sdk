<?php

declare(strict_types=1);

namespace Transloadit\Model\Contracts;

interface StepInterface
{
    public function getName(): string;

    public function setName(string $name): self;

    /**
     * @return array|string
     */
    public function getValue();

    public function setValue($value): self;
}
