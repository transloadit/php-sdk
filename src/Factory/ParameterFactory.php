<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Transloadit\Model\Contracts\ParameterInterface;
use Transloadit\Model\Parameter;

class ParameterFactory
{
    public static function create(array $steps = null, string $templateId = null): ParameterInterface
    {
        return new Parameter($steps, $templateId);
    }
}
