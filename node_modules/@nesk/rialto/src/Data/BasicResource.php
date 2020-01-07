<?php

namespace Nesk\Rialto\Data;

use Nesk\Rialto\Traits\{IdentifiesResource, CommunicatesWithProcessSupervisor};
use Nesk\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcessSupervisor};

class BasicResource implements ShouldIdentifyResource, ShouldCommunicateWithProcessSupervisor, \JsonSerializable
{
    use IdentifiesResource, CommunicatesWithProcessSupervisor;

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): ResourceIdentity
    {
        return $this->getResourceIdentity();
    }
}
