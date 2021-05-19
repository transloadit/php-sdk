<?php

declare(strict_types=1);

namespace Transloadit\Model\Contracts;

use Generator;

interface ParameterInterface
{
    public function getAuth(): AuthInterface;

    public function setAuth(AuthInterface $auth): self;

    /**
     * @return StepInterface[]
     */
    public function getSteps(): array;

    public function getStepsData(): Generator;

    public function addStep(StepInterface $step): self;

    public function getTemplateId(): ?string;

    public function setTemplateId(?string $templateId): self;

    public function getNotifyUrl(): ?string;

    public function setNotifyUrl(?string $notifyUrl): self;

    public function getFields(): ?array;

    public function setFields(?array $fields): self;

    public function getAllowStepsOverride(): ?bool;

    public function setAllowStepsOverride(?bool $allowStepsOverride): self;
}
