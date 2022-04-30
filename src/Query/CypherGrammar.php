<?php

namespace Vinelab\NeoEloquent\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Vinelab\NeoEloquent\DSLContext;
use Vinelab\NeoEloquent\DSLGrammar;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use function array_map;
use function implode;

class CypherGrammar extends Grammar
{
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
        return $this->dsl->compileSelect($query)->toQuery();
    }

    public function compileWheres(Builder $query): string
    {
        return $this->dsl->compileWheres($query)->toQuery();
    }

    public function prepareBindingForJsonContains($binding): string
    {
        return $this->dsl->prepareBindingForJsonContains($binding);
    }

    /**
     * @param  string  $seed
     */
    public function compileRandom($seed): string
    {
        return Query::function()::raw('rand', [])->toQuery();
    }

    public function compileExists(Builder $query): string
    {
        return $this->dsl->compileExists($query)->toQuery();
    }

    public function compileInsert(Builder $query, array $values): string
    {
        return $this->dsl->compileInsertOrIgnore($query, $values)->toQuery();
    }

    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        return $this->dsl->compileInsertOrIgnore($query, $values)->toQuery();
    }

    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        return $this->dsl->compileInsertGetId($query, $values, $sequence)->toQuery();
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): string
    {
        return $this->dsl->compileInsertUsing($query, $columns, $sql)->toQuery();
    }

    public function compileUpdate(Builder $query, array $values): string
    {
        return $this->dsl->compileUpdate($query, $values)->toQuery();
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        return $this->dsl->compileUpsert($query, $values, $uniqueBy, $update)->toQuery();
    }

    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        return $this->dsl->prepareBindingsForUpdate($bindings, $values);
    }

    public function compileDelete(Builder $query): string
    {
        return $this->dsl->compileDelete($query)->toQuery();
    }

    public function prepareBindingsForDelete(array $bindings): array
    {
        return $this->dsl->prepareBindingsForDelete($bindings);
    }

    /**
     * @return string[]
     */
    public function compileTruncate(Builder $query): array
    {
        return array_map([$this, 'getValue'], $this->dsl->compileTruncate($query));
    }

    /**
     * @return bool
     */
    public function supportsSavepoints(): bool
    {
        return $this->dsl->supportsSavepoints();
    }

    /**
     * @param  string  $name
     */
    public function compileSavepoint($name): string
    {
        return $this->dsl->compileSavepoint($name);
    }

    /**
     * @param  string  $name
     */
    public function compileSavepointRollBack($name): string
    {
        return $this->dsl->compileSavepointRollBack($name);
    }

    public function getOperators(): array
    {
        return $this->dsl->getOperators();
    }

    public function getBitwiseOperators(): array
    {
        return $this->dsl->getBitwiseOperators();
    }

    public function wrapArray(array $values): array
    {
        return array_map(static fn ($x) => $x->toQuery(), $this->dsl->wrapArray($values)->getExpressions());
    }

    /**
     * @param Expression|QueryConvertable|string $table
     */
    public function wrapTable($table): string
    {
        return $this->dsl->wrapTable($table)->toQuery();
    }

    /**
     * @param Expression|string  $value
     * @param  bool  $prefixAlias
     */
    public function wrap($value, $prefixAlias = false): string
    {
        return $this->dsl->wrap($value, $prefixAlias)->toQuery();
    }

    public function columnize(array $columns): string
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    public function parameterize(array $values): string
    {
        return implode(', ', array_map(static fn (Parameter $x) => $x->toQuery(), $this->dsl->parameterize($values)));
    }

    /**
     * @param  mixed  $value
     */
    public function parameter($value, ?DSLContext $context = null): string
    {
        return $this->dsl->parameter($value, $context)->toQuery();
    }

    /**
     * @param  string|array  $value
     */
    public function quoteString($value): string
    {
        return implode(', ', array_map([$this, 'getValue'], $this->dsl->quoteString($value)));
    }

    /**
     * @param  mixed  $value
     */
    public function isExpression($value): bool
    {
        return $this->dsl->isExpression($value);
    }

    /**
     * @param Expression|QueryConvertable $expression
     *
     * @return mixed
     */
    public function getValue($expression)
    {
        return $this->dsl->getValue($expression);
    }

    /**
     * Get the format for database stored dates.
     */
    public function getDateFormat(): string
    {
        return $this->dsl->getDateFormat();
    }

    /**
     * Get the grammar's table prefix.
     */
    public function getTablePrefix(): string
    {
        return $this->dsl->getTablePrefix();
    }

    /**
     * Set the grammar's table prefix.
     */
    public function setTablePrefix($prefix): self
    {
        $this->dsl->setTablePrefix($prefix);

        return $this;
    }

    public function __construct()
    {
        $this->dsl = new DSLGrammar();
    }

    private DSLGrammar $dsl;
}