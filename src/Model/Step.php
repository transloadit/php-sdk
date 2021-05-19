<?php

declare(strict_types=1);

namespace Transloadit\Model;

use Transloadit\Model\Contracts\StepInterface;

class Step implements StepInterface
{
    public function __construct(string $name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|array
     */
    protected $value;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return StepInterface
     */
    public function setName(string $name): StepInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  array|string $value
     * @return StepInterface
     */
    public function setValue($value): StepInterface
    {
        $this->value = $value;

        return $this;
    }
}
