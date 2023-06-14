<?php

namespace Vinelab\NeoEloquent\Grammars;

use BadMethodCallException;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use PhpGraphGroup\QueryBuilder\QueryStructure;
use Vinelab\NeoEloquent\ParameterStack;
use Vinelab\NeoEloquent\Query\Adapter\IlluminateToQueryStructurePipeline;
use Vinelab\NeoEloquent\Query\Grammar\VariableGrammar;
use WikibaseSolutions\CypherDSL\Query;

class CypherGrammar extends Grammar
{
    public function __construct() { }

    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected $selectComponents = [
        'from', // MATCH for single node
        'joins', // MATCH with relationship and another node

        'wheres', // WHERE
        'havings', // WHERE

        'groups', // WITH and aggregating function
        'aggregate', // WITH and aggregating function

        'columns', // RETURN
        'orders', // ORDER BY
        'limit', // LIMIT
        'offset', // SKIP
    ];

    public function compileSelect(Builder $query): string
    {
        return IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withReturn()
            ->pipe($query)
            ->toCypher();
    }

    public function compileWheres(Builder $query): string
    {
        return IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withReturn()
            ->pipe($query)
            ->toCypher(GrammarPipeline::create()->withWhereGrammar());
    }

    public function prepareBindingForJsonContains($binding): string
    {
        throw new BadMethodCallException('Json contains is not supported in Neo4j');
    }

    public function compileRandom($seed): string
    {
        return 'random()';
    }

    public function compileExists(Builder $query): string
    {
        return IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withReturn()
            ->pipe($query)
            ->toCypher(GrammarPipeline::create()->withWhereGrammar());
    }

    public function compileInsert(Builder $query, array $values): string
    {
        return IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withCreate($values)
            ->pipe($query)
            ->toCypher();
    }

    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        throw new BadMethodCallException('Compile Insert or Ignore not supported by Neo4j');
    }

    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        foreach ($values as $i => $value) {
            $values[$i] = [];
            foreach (explode(',', $sequence) as $j => $key) {
                $values[$i][$key] = $value[$j];
            }
        }
        return IlluminateToQueryStructurePipeline::create()
            ->withCreate($values)
            ->withReturn()
            ->pipe($query)
            ->toCypher();
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): string
    {
        // TODO
        throw new BadMethodCallException('Compile Insert Using not supported yet by driver');
    }

    public function compileUpdate(Builder $query, array $values): string
    {
        $pipeline = IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withSet($values)
            ->withReturn();

        return $this->witCachedParams($query, $this->dsl->compileUpdate(...), $pipeline->decorate(...));
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        $pipeline = IlluminateToQueryStructurePipeline::create($this->variables)
            ->withMatch()
            ->withWheres()
            ->withMerge($values, $uniqueBy, $update)
            ->withReturn();

        return $this->witCachedParams($query, $this->dsl->compileUpsert(...), $pipeline->decorate(...));
    }

    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        return [];
    }

    public function compileDelete(Builder $query): string
    {
        $pipeline = IlluminateToQueryStructurePipeline::create($this->variables)
            ->withMatch()
            ->withWheres()
            ->withDelete()
            ->withReturn();

        return $this->witCachedParams($query, $this->dsl->compileDelete(...), $pipeline->decorate(...));
    }

    public function prepareBindingsForDelete(array $bindings): array
    {
        return [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function compileTruncate(Builder $query): array
    {
        $pipeline = IlluminateToQueryStructurePipeline::create($this->variables)
            ->withMatch()
            ->withDelete();

        return [$this->witCachedParams($query, $this->dsl->compileTruncate(...), $pipeline->decorate(...)) => []];
    }

    public function supportsSavepoints(): bool
    {
        return false;
    }

    /**
     * @param  string  $name
     */
    public function compileSavepoint($name): string
    {
        throw new BadMethodCallException('Savepoints are not supported by this driver.');
    }

    /**
     * @param  string  $name
     */
    public function compileSavepointRollBack($name): string
    {
        throw new BadMethodCallException('Savepoints are not supported by this driver.');
    }

    public function getOperators(): array
    {
        return [
            '=',
            '==',
            '===',
            'CONTAINS',
            'STARTS WITH',
            'ENDS WITH',
            'IN',
            'LIKE',
            '=~',
            '>',
            '>=',
            '<',
            '<=',
            '<>',
            '!=',
            '!==',
        ];
    }

    public function getBitwiseOperators(): array
    {
        return [];
    }

    /**
     * @param  Expression|string  $table
     */
    public function wrapTable($table): string
    {
        if ($table instanceof Expression) {
            $table = (string) $this->getValue($table);
        }

        return $this->variables->toNodeOrRelationship($table)->toQuery();
    }

    public function getTablePrefix(): string
    {
        return $this->variables->getPrefix();
    }

    public function setTablePrefix($prefix): self
    {
        $this->variables->setPrefix($prefix);

        return $this;
    }

    /**
     * @param  callable(QueryStructure): Query  $compilation
     * @param  callable(Builder, QueryStructure): QueryStructure  $queryDecorator
     */
    protected function witCachedParams(Builder $builder, callable $compilation, callable $queryDecorator): string
    {
        $structure = $this->initialiseStructure($builder);

        $structure = $queryDecorator($builder, $structure);

        $tbr = $compilation($structure)->toQuery();

        CypherGrammar::storeParameters($tbr, $structure->parameters);

        return $tbr;
    }

    public static function storeParameters(string $query, ParameterStack $context): void
    {
        CypherGrammar::$contextCache[$query] = $context;
    }

    /**
     * @param string $query
     * @return array<string, mixed>
     */
    public static function getBoundParameters(string $query): array
    {
        return (CypherGrammar::$contextCache[$query] ?? null)?->getParameters() ?? [];
    }

    public function initialiseStructure(Builder $query): QueryStructure
    {
        return new QueryStructure(new ParameterStack(), $this->variables->toNodeOrRelationship($query->from));
    }
}
