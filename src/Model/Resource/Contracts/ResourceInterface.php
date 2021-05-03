<?php

declare(strict_types=1);

namespace Transloadit\Model\Resource\Contracts;

use Transloadit\Model\Parameter;

interface ResourceInterface
{
    public function getParameter(): Parameter;

    public function setParameter(Parameter $parameter): ResourceInterface;
}
