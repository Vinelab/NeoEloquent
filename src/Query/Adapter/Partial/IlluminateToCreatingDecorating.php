<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;
use Vinelab\NeoEloquent\Processors\Processor;
use Vinelab\NeoEloquent\Query\Adapter\Tracer;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use WikibaseSolutions\CypherDSL\Expressions\Procedures\Procedure;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToCreatingDecorating implements IlluminateToQueryStructureDecorator
{
    public function __construct(private readonly array $values, private readonly bool $batch)
    {
    }

    public function decorate(IlluminateBuilder $illuminateBuilder, CypherBuilder $cypherBuilder): void
    {
        $values = $this->values;

        // First, we detect if this method is delegated by a BelongsToMany relationship.
        // We must also make sure there are actual nodes being created.
        // We will connect the parent and related nodes with the relationship.
        $object = Tracer::isInBelongsToManyWithRelationship($illuminateBuilder);
        if (count($values) > 0 && $object !== null) {

            // In this case we will not create another node, but rather connect the two nodes with a relationship.
            // The problem here is that we will create an Unwind loop and match the parent and related nodes for
            // each connection.
            $qualifiedParentKeyName = $object->getQualifiedParentKeyName();
            $qualifiedRelatedKeyName = $object->getQualifiedRelatedKeyName();

            $cypherBuilder->whereEquals($qualifiedParentKeyName, new RawExpression('toCreate["parent"]'))
                ->andWhereEquals($qualifiedRelatedKeyName, new RawExpression('toCreate["related"]'));

            $creating = [];

            // Because the connection is now embedded into the relationship, we do not need
            // to embed the keys into the properties of the relationship itself anymore.
            foreach ($values as &$row) {
                $toCreate = [];
                $toCreate['parent'] = $row[$object->getForeignPivotKeyName()];
                $toCreate['related'] = $row[$object->getRelatedPivotKeyName()];

//                unset($row[$object->getForeignPivotKeyName()]);
//                unset($row[$object->getRelatedPivotKeyName()]);

                $toCreate['values'] = $row;
                $creating[] = $toCreate;
            }


            [1 => $name] = Processor::fromToName($illuminateBuilder);

            foreach (array_keys($values[0]) as $column) {
                $original = $column;
                if (!str_contains($column, '.')) {
                    $column = "$name.$column";
                }

                $cypherBuilder->creating([
                    Processor::standardiseColumn($column) => new RawExpression("toCreate['values']['$original']")
                ]);
            }

            $cypherBuilder->getStructure()->parameters->add($creating, 'toCreate');


            return;
        }

        if ($this->batch) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $cypherBuilder->batchCreating($values);
        } else {
            $cypherBuilder->creating($values);
        }
    }
}
