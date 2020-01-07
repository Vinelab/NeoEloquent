<?php

namespace Nesk\Rialto\Data;

use Nesk\Rialto\Exceptions\Node\Exception;
use Nesk\Rialto\Interfaces\ShouldHandleProcessDelegation;
use Nesk\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcessSupervisor};

trait UnserializesData
{
    /**
     * Unserialize a value.
     */
    protected function unserialize($value)
    {
        if (!is_array($value)) {
            return $value;
        } else {
            if (($value['__rialto_error__'] ?? false) === true) {
                return new Exception($value, $this->options['debug']);
            } else if (($value['__rialto_resource__'] ?? false) === true) {
                if ($this->delegate instanceof ShouldHandleProcessDelegation) {
                    $classPath = $this->delegate->resourceFromOriginalClassName($value['class_name'])
                        ?: $this->delegate->defaultResource();
                } else {
                    $classPath = $this->defaultResource();
                }

                $resource = new $classPath;

                if ($resource instanceof ShouldIdentifyResource) {
                    $resource->setResourceIdentity(new ResourceIdentity($value['class_name'], $value['id']));
                }

                if ($resource instanceof ShouldCommunicateWithProcessSupervisor) {
                    $resource->setProcessSupervisor($this);
                }

                return $resource;
            } else {
                return array_map(function ($value) {
                    return $this->unserialize($value);
                }, $value);
            }
        }
    }
}
