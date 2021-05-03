<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Transloadit\Model\Contracts\StepInterface;
use Transloadit\Model\Step;

class StepFactory
{
    public static function create(string $name = null, $value = null): StepInterface
    {
        return new Step($name, $value);
    }
}
