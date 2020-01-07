<?php

namespace Nesk\Rialto\Traits;

use Nesk\Rialto\{Instruction, ProcessSupervisor};
use Nesk\Rialto\Interfaces\ShouldIdentifyResource;

trait CommunicatesWithProcessSupervisor
{
    /**
     * The process supervisor to communicate with.
     *
     * @var \Nesk\Rialto\ProcessSupervisor
     */
    protected $processSupervisor;

    /**
     * Whether the current resource should catch instruction errors.
     *
     * @var boolean
     */
    protected $catchInstructionErrors = false;

    /**
    * Get the process supervisor.
    */
    protected function getProcessSupervisor(): ProcessSupervisor
    {
        return $this->processSupervisor;
    }

    /**
     * Set the process supervisor.
     *
     * @throws \RuntimeException if the process supervisor has already been set.
     */
    public function setProcessSupervisor(ProcessSupervisor $processSupervisor): void
    {
        if ($this->processSupervisor !== null) {
            throw new RuntimeException('The process supervisor has already been set.');
        }

        $this->processSupervisor = $processSupervisor;
    }

    /**
     * Clone the resource and catch its instruction errors.
     */
    protected function createCatchingResource()
    {
        $resource = clone $this;

        $resource->catchInstructionErrors = true;

        return $resource;
    }

    /**
     * Proxy an action.
     */
    protected function proxyAction(string $actionType, string $name, $value = null)
    {
        switch ($actionType) {
            case Instruction::TYPE_CALL:
                $value = $value ?? [];
                $instruction = Instruction::withCall($name, ...$value);
                break;
            case Instruction::TYPE_GET:
                $instruction = Instruction::withGet($name);
                break;
            case Instruction::TYPE_SET:
                $instruction = Instruction::withSet($name, $value);
                break;
        }

        $identifiesResource = $this instanceof ShouldIdentifyResource;

        $instruction->linkToResource($identifiesResource ? $this : null);

        if ($this->catchInstructionErrors) {
            $instruction->shouldCatchErrors(true);
        }

        return $this->getProcessSupervisor()->executeInstruction($instruction);
    }

    /**
     * Proxy the string casting to the process supervisor.
     */
    public function __toString(): string
    {
        return $this->proxyAction(Instruction::TYPE_CALL, 'toString');
    }

    /**
     * Proxy the method call to the process supervisor.
     */
    public function __call(string $name, array $arguments)
    {
        return $this->proxyAction(Instruction::TYPE_CALL, $name, $arguments);
    }

    /**
     * Proxy the property reading to the process supervisor.
     */
    public function __get(string $name)
    {
        if ($name === 'tryCatch' && !$this->catchInstructionErrors) {
            return $this->createCatchingResource();
        }

        return $this->proxyAction(Instruction::TYPE_GET, $name);
    }

    /**
     * Proxy the property writing to the process supervisor.
     */
    public function __set(string $name, $value)
    {
        return $this->proxyAction(Instruction::TYPE_SET, $name, $value);
    }
}
