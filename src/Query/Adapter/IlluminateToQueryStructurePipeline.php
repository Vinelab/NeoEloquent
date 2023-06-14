<?php

namespace Vinelab\NeoEloquent\Query\Adapter;

use Illuminate\Contracts\Database\Query\Builder as SqlBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\WhereGrammar;
use PhpGraphGroup\CypherQueryBuilder\Builders\GraphPatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;
use Vinelab\NeoEloquent\Query\Adapter\Partial\IlluminateToWhereDecorator;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator as Decorator;

class IlluminateToQueryStructurePipeline
{
    /**
     * @param  list<Decorator>  $decorators
     */
    private function __construct(
        private readonly array $decorators
    ) {
    }

    public function pipe(Builder $illuminateBuilder): QueryBuilder
    {
        [$labelOrType, $name] = $this->extractLabelOrTypeAndName($illuminateBuilder);

        $patterns = GraphPatternBuilder::from($labelOrType, $name, $this->containsLeftJoin($illuminateBuilder));

        $this->decorateBuilder($illuminateBuilder, $patterns);

        $patterns->end();

        $builder = QueryBuilder::from($patterns);

        foreach ($this->decorators as $decorator) {
            $decorator->decorate($illuminateBuilder, $builder);
        }

        return $builder;
    }

    private function decorateBuilder(SqlBuilder $builder, PatternBuilder $patternBuilder): void
    {
        /** @var JoinClause $join */
        foreach ($builder->joins as $join) {
            [$labelOrType, $name] = $this->extractLabelOrTypeAndName($join);
            $optional = $join->type === 'right';

            if (str_starts_with($join->table, '<') || str_ends_with($join->table, '>')) {
                $child = $patternBuilder->addRelationship($labelOrType, $name, optional: $optional);
            } else {
                $child = $patternBuilder->addChildNode($labelOrType, $name, optional: $optional);
            }

            $this->decorateBuilder($join, $patternBuilder);

            $child->end();
        }
    }

    /**
     * @param SqlBuilder $illuminateBuilder
     * @return array{0: string, 1:string|null}
     */
    public function extractLabelOrTypeAndName(SqlBuilder $illuminateBuilder): array
    {
        preg_match('/(?<label>\w+)(?:\s+as\s+(?<name>\w+))?/i', $illuminateBuilder->from, $matches);

        $label = $matches['label'];
        $name = $matches['name'] ?? null;

        return [$label, $name];
    }


    private function containsLeftJoin(SqlBuilder $builder): bool
    {
        foreach ($builder->joins as $join) {
            if ($join->type === 'left') {
                return true;
            }
        }

        return false;
    }


    public static function create(): self
    {
        return new self([]);
    }

    public function withWheres(): self
    {
        return new self([...$this->decorators, ...[new IlluminateToWhereDecorator()]]);
    }

    public function withReturn(): self
    {
        return new self();
    }

    public function withMergeAdapter(): self
    {
        return new self();
    }

    public function withCreate(array $values): self
    {
        return new self($this->variables, array_merge($this->decorators, [new IlluminateToCreateDecorator($this->variables, $values)]));
    }

    public function withSet(array $values): self
    {

    }

    public function withMerge(array $values, array $uniqueBY, array $update): self
    {

    }

    public function withDelete(): self
    {

    }
}
