<?php

declare(strict_types=1);

namespace Transloadit\Service;

use Transloadit\Enum\Status;
use Transloadit\Model\Resource\Contracts\AssemblyInterface;
use Transloadit\Model\Resource\Contracts\ResourceInterface;

class AssemblyWaitForResourceService extends AssemblyResourceService
{
    public function create(AssemblyInterface $assembly): ResourceInterface
    {
        $assembly = parent::create($assembly);

        while (in_array($assembly->getStatus(), [Status::ASSEMBLY_EXECUTING, Status::ASSEMBLY_UPLOADING])) {
            sleep(1);
            $assembly = $this->getById($assembly->getId());
        }

        return $assembly;
    }
}
