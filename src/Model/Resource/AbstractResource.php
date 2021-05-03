<?php

declare(strict_types=1);

namespace Transloadit\Model\Resource;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Transloadit\Model\Parameter;
use Transloadit\Model\Resource\Contracts\ResourceInterface;

abstract class AbstractResource implements ResourceInterface
{
    /**
     * @Groups({"read", "write"})
     * @SerializedName("params")
     * @var Parameter
     */
    private $parameter;

    public function __construct(Parameter $parameter = null)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @param Parameter $parameter
     * @return ResourceInterface
     */
    public function setParameter(Parameter $parameter): ResourceInterface
    {
        $this->parameter = $parameter;
        return $this;
    }
}
