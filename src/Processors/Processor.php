<?php

namespace Vinelab\NeoEloquent\Processors;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Laudis\Neo4j\Contracts\HasPropertiesInterface;
use Laudis\Neo4j\Types\DateTime;
use Laudis\Neo4j\Types\DateTimeZoneId;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use function str_contains;

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
        $name = null;

        if ($builder instanceof JoinClause) {
            $from = $builder->from ?? $builder->table;
        } elseif ($builder instanceof Builder) {
            $from = $builder->from;
        } else {
            $from = $builder;
        }

        if (str_contains($from, ' as ')) {
            [$label, $name] = explode(' as ', $from, 2);
        } else {
            $label = $from;
        }

        if (str_starts_with($label, '<') || str_ends_with($label, '>')) {
            $target = 'relationship';
        } else {
            $target = 'node';
        }

        [$labelOrType, $name] = (new GraphPattern())->decode($label, $target, $direction, $name);

        return [$labelOrType[0], $name, $target === 'relationship', $direction];
    }

    public static function standardiseColumn(string $column): string
    {
        if (! str_contains($column, '.')) {
            return $column;
        }

        [$table, $column] = explode('.', $column, 2);
        [1 => $name] = Processor::fromToName($table);

        return "$name.$column";
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

    public function processColumnListing($results): array
    {
        return Arr::pluck($results, 'column_name');
    }
}
