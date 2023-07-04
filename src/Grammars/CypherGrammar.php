<?php

namespace Vinelab\NeoEloquent\Grammars;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use RuntimeException;
use Vinelab\NeoEloquent\Query\Adapter\IlluminateToQueryStructurePipeline;
use Vinelab\NeoEloquent\Query\Adapter\Tracer;
use WeakReference;

use function array_key_first;
use function is_array;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress InvalidArgument
 */
class CypherGrammar extends Grammar
{
    /** @var array<string, array<string, mixed>|WeakReference<Builder>> */
    private static array $cache = [];

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
        return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withReturn()
            ->pipe($query)
            ->toCypher()
        );
    }

    public function compileWheres(Builder $query): string
    {
        return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
                                                          ->withWheres()
                                                          ->withReturn()
                                                          ->pipe($query)
                                                          ->toCypher(GrammarPipeline::create()->withWhereGrammar())
        );
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
        return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withReturn()
            ->pipe($query)
            ->toCypher(GrammarPipeline::create()->withWhereGrammar())
        );
    }

    public function compileInsert(Builder $query, array $values): string
    {
        $prefix = '';
        if (Tracer::isInBelongsToManyWithRelationship($query)) {
            $prefix = 'UNWIND $toCreate AS toCreate ';
        }

        $pipeline = IlluminateToQueryStructurePipeline::create()->withWheres();

        if (is_int(array_key_first($values))) {
            $pipeline = $pipeline ->withBatchCreate($values);
        } else {
            $pipeline = $pipeline->withCreate($values);
        }

        return $this->cache($query, static fn () => $prefix . $pipeline->pipe($query)->toCypher());
    }

    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        throw new BadMethodCallException('Compile Insert or Ignore not supported by Neo4j');
    }

    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withCreate($values)
            ->withReturn()
            ->pipe($query)
            ->toCypher()
        );
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): string
    {
        throw new RuntimeException('This database engine does not support is compile insert using yet');
    }

    public function compileUpdate(Builder $query, array $values): string
    {
         return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withSet($values)
            ->withReturn()
            ->pipe($query)
            ->toCypher()
         );
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
         return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withMerge($values, $uniqueBy, $update)
            ->withReturn()
            ->pipe($query)
            ->toCypher()
         );
    }

    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        return [];
    }

    public function compileDelete(Builder $query): string
    {
         return $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withWheres()
            ->withDelete()
            ->withReturn()
            ->pipe($query)
            ->toCypher()
        );
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
        $cypher = $this->cache($query, static fn () => IlluminateToQueryStructurePipeline::create()
            ->withDelete()
            ->pipe($query)
            ->toCypher()
        );

        return [ $cypher => []];
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

    public function compileJsonValueCast($value): string
    {
        throw new RuntimeException('This database engine does not support JSON value cast operations.');
    }

    public function whereFullText(Builder $query, $where)
    {
        throw new RuntimeException('This database engine does not support Full Text operations yet.');
    }

    public function whereExpression(Builder $query, $where)
    {
        throw new RuntimeException('This database engine does not support solo Where expressions yet.');
    }

    public function wrapArray(array $values)
    {
        throw new RuntimeException('This database engine does not support solo wrap Array expressions yet.');
    }

    public function wrap($value, $prefixAlias = false)
    {
        throw new RuntimeException('This database engine does not support solo wrap expressions.');
    }

    public function columnize(array $columns)
    {
        throw new RuntimeException('This database engine does not support wrap expressions yet.');
    }

    public function parameterize(array $values)
    {
        throw new RuntimeException('This database engine does not support solo parametrization yet.');
    }

    public function parameter($value)
    {
        throw new RuntimeException('This database engine does not support solo parametrization yet.');
    }

    public function quoteString($value)
    {
        throw new RuntimeException('This database engine does not support string quotation yet.');
    }

    public function escape($value, $binary = false)
    {
        throw new RuntimeException('This database engine does not support string escapes yet.');
    }

    public function isExpression($value)
    {
        throw new RuntimeException('This database engine does not support is expression yet.');
    }

    public function getValue($expression)
    {
        throw new RuntimeException('This database engine does not support is get value yet.');
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    public function getTablePrefix()
    {
        throw new RuntimeException('This database engine does not support is table prefixes yet.');
    }

    public function setTablePrefix($prefix)
    {
        throw new RuntimeException('This database engine does not support is table prefixes yet.');
    }

    public function wrapTable($table)
    {
        throw new RuntimeException('This database engine does not support is table wrapping yet.');
    }

    /**
     * @param Closure():string $function
     */
    private function cache(Builder $query, Closure $function): string
    {
        $cypher               = $function();

        self::$cache[$cypher] = WeakReference::create($query);

        return $cypher;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getBindings(string $cypher): array
    {
        $reference = self::$cache[$cypher] ?? null;
        if (is_array($reference)) {
            return $reference;
        }

        $builder = $reference?->get();

        if ($builder instanceof Builder) {
            return IlluminateToQueryStructurePipeline::getBindings($builder);
        }

        return [];
    }

    /**
     * @param array<string, mixed> $bindings
     */
    public static function setBindings(string $cypher, array $bindings): void
    {
        self::$cache[$cypher] = $bindings;
    }
}
