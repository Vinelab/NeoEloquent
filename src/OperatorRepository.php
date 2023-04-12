<?php

namespace Vinelab\NeoEloquent;

use function array_key_exists;
use WikibaseSolutions\CypherDSL\Addition;
use WikibaseSolutions\CypherDSL\AndOperator;
use WikibaseSolutions\CypherDSL\Assignment;
use WikibaseSolutions\CypherDSL\Contains;
use WikibaseSolutions\CypherDSL\Division;
use WikibaseSolutions\CypherDSL\EndsWith;
use WikibaseSolutions\CypherDSL\Equality;
use WikibaseSolutions\CypherDSL\Exists;
use WikibaseSolutions\CypherDSL\Exponentiation;
use WikibaseSolutions\CypherDSL\GreaterThan;
use WikibaseSolutions\CypherDSL\GreaterThanOrEqual;
use WikibaseSolutions\CypherDSL\In;
use WikibaseSolutions\CypherDSL\Inequality;
use WikibaseSolutions\CypherDSL\LessThan;
use WikibaseSolutions\CypherDSL\LessThanOrEqual;
use WikibaseSolutions\CypherDSL\Minus;
use WikibaseSolutions\CypherDSL\Modulo;
use WikibaseSolutions\CypherDSL\Multiplication;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\OrOperator;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\StartsWith;
use WikibaseSolutions\CypherDSL\Subtraction;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\XorOperator;

final class OperatorRepository
{
    private const OPERATORS = [
        '+' => Addition::class,
        'AND' => AndOperator::class,
        '=' => Equality::class,
        '+=' => Assignment::class,
        'CONTAINS' => Contains::class,
        '/' => Division::class,
        'ENDS WITH' => EndsWith::class,
        'EXISTS' => Exists::class,
        '^' => Exponentiation::class,
        '>' => GreaterThan::class,
        '>=' => GreaterThanOrEqual::class,
        'IN' => In::class,
        '[x]' => In::class,
        '[x .. y]' => In::class,
        '<>' => Inequality::class,
        '!=' => Inequality::class,
        '<' => LessThan::class,
        '<=' => LessThanOrEqual::class,
        '-' => [Minus::class, Subtraction::class],
        '%' => Modulo::class,
        '*' => Multiplication::class,
        'NOT' => Not::class,
        'OR' => OrOperator::class,
        'STARTS WITH' => StartsWith::class,
        'XOR' => XorOperator::class,
        '=~' => '',
        'IS NULL' => RawExpression::class,
        'IS NOT NULL' => RawExpression::class,
        'RAW' => RawExpression::class,
    ];

    public static function bitwiseOperations(): array
    {
        return ['&', '|', '^', '~', '<<', '>>', '>>>'];
    }

    /**
     * @param  mixed  $lhs
     * @param  mixed  $rhs
     * @return BooleanType
     */
    public static function fromSymbol(string $symbol, $lhs = null, $rhs = null, $insertParenthesis = true): AnyType
    {
        $class = self::OPERATORS[$symbol];

        return new $class($lhs, $rhs, $insertParenthesis);
    }

    public static function symbolExists(string $symbol): bool
    {
        return array_key_exists(strtoupper($symbol), self::OPERATORS);
    }
}
