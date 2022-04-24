<?php

namespace Vinelab\NeoEloquent;

use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;

/**
 * Helper class to reduce excessive if-calls when building where queries.
 */
class EmptyBoolean implements AnyType
{
    public function and(BooleanType $right, bool $insertParentheses = true): BooleanType
    {
        return $right;
    }

    public function or(BooleanType $right, bool $insertParentheses = true): BooleanType
    {
        return $right;
    }

    public function toQuery(): string
    {
        return '';
    }
}