<?php

declare(strict_types=1);

namespace Transloadit\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Transloadit\Model\Contracts\AuthInterface;
use Transloadit\Model\Contracts\ParameterInterface;
use Transloadit\Model\Contracts\StepInterface;
use Generator;

class Parameter implements ParameterInterface
{
    /**
     * @Groups("write")
     * @var AuthInterface
     */
    private $auth;

    /**
     * @var StepInterface[]
     */
    private $steps;

    /**
     * @Groups({"read", "write"})
     * @var string|null
     */
    private $templateId;

    /**
     * @Groups({"read", "write"})
     * @var string|null
     */
    private $notifyUrl;

    /**
     * @Groups({"read", "write"})
     * @var array|null
     */
    private $fields;

    /**
     * @Groups({"read", "write"})
     * @var bool|null
     */
    private $allowStepsOverride;

    /**
     * Parameter constructor.
     * @param Step[] $steps
     * @param string|null $templateId
     */
    public function __construct(array $steps = null, string $templateId = null)
    {
        $this->steps = $steps;
        $this->templateId = $templateId;
    }

    /**
     * @return AuthInterface
     */
    public function getAuth(): AuthInterface
    {
        return $this->auth;
    }

    /**
     * @param AuthInterface $auth
     * @return ParameterInterface
     */
    public function setAuth(AuthInterface $auth): ParameterInterface
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @return Step[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @Groups({"read", "write"})
     * @SerializedName("steps")
     */
    public function getStepsData(): Generator
    {
        foreach ($this->getSteps() as $step) {
            yield $step->getName() => $step->getValue();
        }
    }

    /**
     * @param  StepInterface $step
     * @return ParameterInterface
     */
    public function addStep(StepInterface $step): ParameterInterface
    {
        if (null === $this->steps || false === in_array($step, $this->steps)) {
            $this->steps[] = $step;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    /**
     * @param  string|null $templateId
     * @return ParameterInterface
     */
    public function setTemplateId(?string $templateId): ParameterInterface
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    /**
     * @param  string|null $notifyUrl
     * @return ParameterInterface
     */
    public function setNotifyUrl(?string $notifyUrl): ParameterInterface
    {
        $this->notifyUrl = $notifyUrl;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param  array|null $fields
     * @return ParameterInterface
     */
    public function setFields(?array $fields): ParameterInterface
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAllowStepsOverride(): ?bool
    {
        return $this->allowStepsOverride;
    }

    /**
     * @param  bool|null $allowStepsOverride
     * @return ParameterInterface
     */
    public function setAllowStepsOverride(?bool $allowStepsOverride): ParameterInterface
    {
        $this->allowStepsOverride = $allowStepsOverride;

        return $this;
    }
}
