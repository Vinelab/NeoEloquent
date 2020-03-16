<?php

namespace Vinelab\NeoEloquent\Traits;

use GraphAware\Common\Result\AbstractRecordCursor as Result;
use GraphAware\Common\Result\RecordViewInterface;
use GraphAware\Common\Type\Node;
use GraphAware\Common\Type\Relationship;

trait ResultTrait
{
    /**
     * @param Result $result
     *
     * @return \GraphAware\Common\Result\RecordViewInterface[]
     */
    public function getResultRecords(Result $result)
    {
        return $result->getRecords();
    }

    /**
     * @param array $recordViews
     *
     * @return array
     */
    public function getRecordsByPlaceholders(Result $result)
    {
        $recordViews = $this->getResultRecords($result);

        $recordsByKeys = [];
        foreach ($recordViews as $recordView) {
            if ($recordView instanceof RecordViewInterface) {
                $keys = $recordView->keys();
                foreach ($keys as $key) {
                    $recordsByKeys[$key][] = $recordView->value($key);
                }
            }
        }

        return $recordsByKeys;
    }

    /**
     * @param array $recordsByPlaceholders
     *
     * @return array
     */
    public function getRelationshipRecords(Result $result)
    {
        $relationships = [];

        $recordViews = $this->getResultRecords($result);

        foreach ($recordViews as $recordView) {
            if ($recordView instanceof RecordViewInterface) {
                $keys = $recordView->keys();
                foreach ($keys as $key) {
                    $record = $recordView->value($key);
                    if ($record instanceof Relationship) {
                        $relationships[] = $record;
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * @param array $recordsByPlaceholders
     *
     * @return array
     */
    public function getNodeRecords(Result $result)
    {
        $nodes = [];

        $recordViews = $this->getResultRecords($result);

        foreach ($recordViews as $recordView) {
            if ($recordView instanceof RecordViewInterface) {
                $keys = $recordView->keys();
                foreach ($keys as $key) {
                    $record = $recordView->value($key);
                    if ($record instanceof Node) {
                        $nodes[] = $record;
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * @param Result $result
     *
     * @return mixed
     */
    public function getSingleItem(Result $result)
    {
        return $this->getRecords($result)->firstRecord()->valueByIndex(0);
    }

    /**
     * @param \GraphAware\Bolt\Result\Type\Relationship $relation
     * @param array                                     $nodes
     * @param string                                    $type
     *
     * @return Node
     */
    public function getNodeByType(Relationship $relation, array $nodes, string $type = 'start')
    {
        if ($type != 'start') {
            $type = 'end';
        }

        $method = $type.'NodeIdentity';

        $id = $relation->{$method}();

        foreach ($nodes as $node) {
            if ($id === $node->identity()) {
                return $node;
            }
        }
    }

    /**
     * @param RecordViewInterface $record
     *
     * @return array
     */
    public function getRecordNodes(RecordViewInterface $record)
    {
        $nodes = [];

        $keys = $record->keys();
        foreach ($keys as $key) {
            $item = $record->get($key);
            if ($item instanceof Node) {
                $nodes[] = $item;
            }
        }

        return $nodes;
    }

    /**
     * @param RecordViewInterface $record
     *
     * @return array
     */
    public function getRecordRelationships(RecordViewInterface $record)
    {
        $relationships = [];

        $keys = $record->keys();
        foreach ($keys as $key) {
            $item = $record->get($key);
            if ($item instanceof Relationship) {
                $relationships[] = $item;
            }
        }

        return $relationships;
    }
}
