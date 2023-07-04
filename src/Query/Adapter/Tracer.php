<?php

namespace Vinelab\NeoEloquent\Query\Adapter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinelab\NeoEloquent\Processors\Processor;

class Tracer
{
    public static function isInBelongsToManyWithRelationship(Builder $builder): BelongsToMany|null
    {
        $trace = debug_backtrace(limit: 14);

        return self::analyseTrace($trace[8] ?? null, $builder) ??
            self::analyseTrace($trace[4] ?? null, $builder) ??
            self::analyseTrace($trace[11] ?? null, $builder) ??
            self::analyseTrace($trace[13] ?? null, $builder);
    }

    private static function analyseTrace(array|null $backtrace, Builder $builder): ?BelongsToMany
    {
        $object = $backtrace['object'] ?? null;
        [2 => $isRelationship] = Processor::fromToName($builder);
        if (!$isRelationship && is_array($builder->joins) && count($builder->joins) === 1) {
            [2 => $isRelationship] = Processor::fromToName($builder->joins[0]->table);
        }

        if ($object instanceof BelongsToMany && $isRelationship) {
            return $object;
        }

        return null;
    }
}