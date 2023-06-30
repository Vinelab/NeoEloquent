<?php

namespace Vinelab\NeoEloquent\Query\Adapter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use PhpGraphGroup\CypherQueryBuilder\Builders\GraphPatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\Common\Parameter;
use PhpGraphGroup\CypherQueryBuilder\Common\ParameterStack;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;
use Vinelab\NeoEloquent\Processors\Processor;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToCreatingDecorating;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToDeletingDecorator;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToMergeDecorator;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToReturnDecorator;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToSettingDecorator;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToUnioningDecorator;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToWhereDecorator;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator as Decorator;
use WeakMap;

class IlluminateToQueryStructurePipeline
{
    /** @var WeakMap<Builder, QueryBuilder>|null */
    private static WeakMap|null $cache = null;

    /**
     * @param  list<Decorator>  $decorators
     */
    private function __construct(
        private readonly array $decorators,
        private readonly ParameterStack $parameterStack
    ) {
    }

    /**
     * @param Builder $builder
     *
     * @return array<string, mixed>
     */
    public static function getBindings(Builder $builder): array
    {
        $bindings = self::getCache()[$builder] ?? null;

        if ($bindings === null) {
            return [];
        }

        /**
         * @psalm-suppress InternalProperty
         * @psalm-suppress InternalMethod
         */
        return array_map(static fn (Parameter $x): mixed => $x->value, $bindings->getStructure()->parameters->getParameters());
    }

    public function pipe(Builder $illuminateBuilder): QueryBuilder
    {
        [$labelOrType, $name, $isRelationship, $direction] = Processor::fromToName($illuminateBuilder);

        if ($isRelationship) {
            $patterns = GraphPatternBuilder::fromRelationship($labelOrType, $name, $direction, $this->containsLeftJoin($illuminateBuilder));
        } else {
            $patterns = GraphPatternBuilder::fromNode($labelOrType, $name, $this->containsLeftJoin($illuminateBuilder));
        }

        $this->decorateBuilder($illuminateBuilder, $patterns);

        $builder = QueryBuilder::from($patterns);

        foreach ($this->parameterStack as $key => $value) {
            $builder->getStructure()->parameters->add($value, $key);
        }

        foreach ($this->decorators as $decorator) {
            $decorator->decorate($illuminateBuilder, $builder);
        }

        self::getCache()[$illuminateBuilder] = $builder;

        foreach ($builder->getStructure()->parameters as $key => $value) {
            $this->parameterStack->add($value, $key);
        }

        return $builder;
    }

    private function decorateBuilder(Builder $builder, PatternBuilder $patternBuilder): void
    {
        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         * @psalm-suppress DocblockTypeContradiction
         * @var JoinClause $join
         */
        foreach (($builder->joins ?? []) as $join) {
            [$labelOrType, $name, $isRelationship, $direction] = Processor::fromToName($join);
            $optional = $join->type === 'left';

            if ($isRelationship) {
                $child = $patternBuilder->addRelationship($labelOrType, $name, $direction, $optional);
            } else {
                $child = $patternBuilder->addChildNode($labelOrType, $name, $optional);
            }

            $this->decorateBuilder($join, $patternBuilder);

            $child->end();
        }
    }


    private function containsLeftJoin(Builder $builder): bool
    {
        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         * @psalm-suppress DocblockTypeContradiction
         */
        foreach (($builder->joins ?? []) as $join) {
            if ($join->type === 'left') {
                return true;
            }
        }

        return false;
    }


    public static function create(ParameterStack|null $stack = null): self
    {
        return new self([], $stack ?? new ParameterStack());
    }

    public function withWheres(): self
    {
        return new self([...$this->decorators, ...[new IlluminateToWhereDecorator()]], $this->parameterStack);
    }

    public function withReturn(): self
    {
        return new self([... $this->decorators, ...[new IlluminateToReturnDecorator()]], $this->parameterStack);
    }

    public function withCreate(array $values): self
    {
        return new self([... $this->decorators, ...[new IlluminateToCreatingDecorating($values, false)]], $this->parameterStack);
    }

    public function withBatchCreate(array $values): self
    {
        return new self([... $this->decorators, ...[new IlluminateToCreatingDecorating($values, true)]], $this->parameterStack);
    }

    public function withSet(array $values): self
    {
        return new self([... $this->decorators, ...[new IlluminateToSettingDecorator($values)]], $this->parameterStack);
    }

    public function withMerge(array $values, array $uniqueBy, array $update): self
    {
        return new self([... $this->decorators, ...[new IlluminateToMergeDecorator($values, $uniqueBy, $update)]], $this->parameterStack);
    }

    public function withParameterStack(ParameterStack $stack): self
    {
        return new self($this->decorators, $stack);
    }

    public function withDelete(): self
    {
        return new self([... $this->decorators, ...[new IlluminateToDeletingDecorator()]], $this->parameterStack);
    }

    public function withUnion(): self
    {
        return new self([... $this->decorators, ...[new IlluminateToUnioningDecorator(
            static fn () => IlluminateToQueryStructurePipeline::create()->withWheres()->withReturn()
        )]], $this->parameterStack);
    }

    /**
     * @return WeakMap<Builder, QueryBuilder>
     */
    private static function getCache(): WeakMap
    {
        if (self::$cache === null) {
            /** @var WeakMap<Builder,QueryBuilder> */
            self::$cache = new WeakMap();
        }

        return self::$cache;
    }
}
