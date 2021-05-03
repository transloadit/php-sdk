<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Transloadit\Model\Contracts\ParameterInterface;
use Transloadit\Model\Resource\Assembly;
use Transloadit\Model\Resource\Contracts\AssemblyInterface;

class AssemblyFactory
{
    public static function create(ParameterInterface $parameter = null): AssemblyInterface
    {
        return new Assembly($parameter);
    }
}
