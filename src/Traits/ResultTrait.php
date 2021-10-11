<?php

namespace Vinelab\NeoEloquent\Traits;

use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;
use Laudis\Neo4j\Types\Relationship;
use RuntimeException;

trait ResultTrait
{
    public function getRecordsByPlaceholders(CypherList $result): array
    {
        $recordsByKeys = [];
        /**
         * @var CypherMap $recordView
         */
        foreach ($result as $recordView) {
            foreach ($recordView as $key => $value) {
                $recordsByKeys[$key] = $recordsByKeys[$key] ?? [];
                $recordsByKeys[$key][] = $value;
            }
        }

        return $recordsByKeys;
    }

    public function getRelationshipRecords(CypherList $results): array
    {
        $relationships = [];

        foreach ($results as $record) {
            $relationships = array_merge($relationships, $this->getRecordRelationships($record));
        }

        return $relationships;
    }

    public function getNodeRecords(CypherList $result): array
    {
        $nodes = [];

        foreach ($result as $record) {
            $nodes = array_merge($nodes, $this->getRecordNodes($record));
        }

        return $nodes;
    }

    /**
     * @param CypherList $result
     * @return mixed
     */
    public function getSingleItem(CypherList $result)
    {
        /** @var CypherMap $map */
        $map = $result->first();
        return $map->first()->getValue();
    }

    public function getNodeByType(Relationship $relation, array $nodes, string $type = 'start'): Node
    {
        if($type === 'start') {
            $id = $relation->getStartNodeId();
        } else {
            $id = $relation->getEndNodeId();
        }

        /** @var Node $node */
        foreach ($nodes as $node) {
            if($id === $node->getId()) {
                return $node;
            }
        }

        throw new RuntimeException('Cannot find node with id: ' . $node->getId());
    }

    /**
     * @return list<Node>
     */
    public function getRecordNodes(CypherMap $record): array
    {
        $nodes = [];

        foreach ($record as $value) {
            if($value instanceof Node) {
                $nodes[] = $value;
            }
        }

        return $nodes;
    }

    /**
     * @return list<Node>
     */
    public function getRecordRelationships(CypherMap $record): array
    {
        $relationships = [];

        foreach ($record as $item) {
            if($item instanceof Relationship) {
                $relationships[] = $item;
            }
        }

        return $relationships;
    }
}