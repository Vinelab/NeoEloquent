<?php

namespace Vinelab\NeoEloquent\Query\Adapter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tracer
{
    public static function isInBelongsToManyWithRelationship(Builder $builder): BelongsToMany|null
    {
        $backtrace = debug_backtrace(limit: 9)[8] ?? null;
        $object = $backtrace['object'] ?? null;
        if ($object instanceof BelongsToMany && (str_contains($builder->from, '<') || str_contains($builder->from, '>'))) {
            return $object;
        }

        return null;
    }
}