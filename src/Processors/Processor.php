<?php

namespace Vinelab\NeoEloquent\Processors;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Laudis\Neo4j\Contracts\HasPropertiesInterface;
use Laudis\Neo4j\Types\DateTime;
use Laudis\Neo4j\Types\DateTimeZoneId;
use PhpGraphGroup\CypherQueryBuilder\Builders\GraphPatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\Common\PropertyRelationship;

use function preg_match;
use function str_contains;
use function str_replace;

class Processor extends \Illuminate\Database\Query\Processors\Processor
{
    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress DocblockTypeContradiction
     *
     * @return array{0: string, 1: string, 2: bool, 3: mixed}
     */
    public static function fromToName(Builder|JoinClause|string $builder): array
    {
        if ($builder instanceof JoinClause) {
            $from = $builder->from ?? $builder->table;
        } else if ($builder instanceof Builder) {
            $from = $builder->from;
        } else {
            $from = $builder;
        }

        preg_match('/(?<label>\w+)(?:\s+as\s+(?<name>\w+))?/i', $from, $matches);

        $label = $matches['label'] ?? null;
        $name = $matches['name'] ?? null;


        $nodeOrRelationship = GraphPatternBuilder::from($label, $name)->getPattern()->chunk('match')[0];
        if ($nodeOrRelationship instanceof PropertyRelationship) {
            return [ $nodeOrRelationship->types[0], $nodeOrRelationship->name->name, true, $nodeOrRelationship->direction];
        }

        return [$nodeOrRelationship->labels[0], $nodeOrRelationship->name->name, false, null];
    }

    public function processSelect(Builder $query, $results): array
    {
        $tbr = [];
        [1 => $from] = self::fromToName($query);
        foreach ($results as $row) {
            $processedRow = [];

            foreach ($row as $key => $value) {
                if ($value instanceof HasPropertiesInterface) {
                    $preface = $key.'.';
                    if ($key === $from) {
                        $preface = '';
                    }

                    foreach ($value->getProperties() as $prop => $x) {
                        $processedRow[$preface.$prop] = $this->filterDateTime($x);
                    }
                } else {
                    $processedRow[$key] = $this->filterDateTime($value);
                }
            }
            $tbr[] = $processedRow;
        }

        return $tbr;
    }

    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null): mixed
    {
        $prop = Arr::first($query->getConnection()->selectOne($sql, $values, false));
        if (is_string($sequence) && $prop instanceof HasPropertiesInterface) {
            return $prop->getProperties()->get($sequence);
        }

        return $prop;
    }

    private function filterDateTime(mixed $x): mixed
    {
        if ($x instanceof DateTimeZoneId || $x instanceof DateTime) {
            return $x->toDateTime();
        }

        return $x;
    }
}
