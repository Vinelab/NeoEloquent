<?php

namespace Vinelab\NeoEloquent;

use WikibaseSolutions\CypherDSL\Alias;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Variable;

class DSLContext
{
    /** @var array<string, mixed> */
    private array $parameters = [];
    /** @var list<Variable> */
    private array $withStack = [];
    private int $subResultCounter = 0;

    /**
     * @param mixed $value
     */
    public function addParameter($value): Parameter
    {
        $param = Query::parameter('param' . count($this->parameters));

        $this->parameters[$param->getName()] = $value;

        return $param;
    }

    public function createSubResult(AnyType $type): Alias
    {
        $subresult = new Alias($type, new Variable('sub'.$this->subResultCounter));

        ++$this->subResultCounter;

        return $subresult;
    }

    public function addVariable(Variable $variable): void
    {
        $this->withStack[] = $variable;
    }

    public function popVariable(): void
    {
        array_pop($this->withStack);
    }

    /**
     * @return list<Variable>
     */
    public function getVariables(): array
    {
        return $this->withStack;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}