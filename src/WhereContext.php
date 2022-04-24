<?php

namespace Vinelab\NeoEloquent;

use Vinelab\NeoEloquent\Query\Builder;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;

class WhereContext
{
    private Builder $builder;
    private array $where;
    private Query $query;
    private DSLContext $context;
    /** @var EmptyBoolean|BooleanType */
    private $boolean;

    /**
     * @param BooleanType|EmptyBoolean $boolean
     */
    public function __construct(Builder $builder, array $where, Query $query, DSLContext $context, $boolean)
    {
        $this->builder = $builder;
        $this->where = $where;
        $this->query = $query;
        $this->context = $context;
        $this->boolean = $boolean;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function getWhere(): array
    {
        return $this->where;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getContext(): DSLContext
    {
        return $this->context;
    }

    /**
     * @return EmptyBoolean|BooleanType
     */
    public function getBoolean()
    {
        return $this->boolean;
    }
}