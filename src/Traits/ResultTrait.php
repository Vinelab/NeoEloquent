<?php

namespace Vinelab\NeoEloquent\Traits;

use GraphAware\Common\Type\Node;
use GraphAware\Common\Type\Relationship;
use GraphAware\Neo4j\Client\Formatter\Result;
use GraphAware\Common\Result\RecordViewInterface;

trait ResultTrait
{
    /**
     * @param Result $result
     * @return \GraphAware\Neo4j\Client\Formatter\RecordView[]
     */
    public function getResultRecords(Result $result)
    {
        return $result->getRecords();
    }

    /**
     * @param array $recordViews
     * @return array
     */
    public function getRecordsByPlaceholders(Result $result)
    {
        $recordViews = $this->getResultRecords($result);

        $recordsByKeys = [];
        foreach ($recordViews as $recordView) {
            if($recordView instanceof RecordViewInterface) {
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
     * @return array
     */
    public function getRelationshipRecords(Result $result)
    {
        $relationships = [];

        $recordViews = $this->getResultRecords($result);

        foreach ($recordViews as $recordView) {
            if($recordView instanceof RecordViewInterface) {
                $keys = $recordView->keys();
                foreach ($keys as $key) {
                    $record = $recordView->value($key);
                    if($record instanceof Relationship) {
                        $relationships[] = $record;
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * @param array $recordsByPlaceholders
     * @return array
     */
    public function getNodeRecords(Result $result)
    {
        $nodes = [];

        $recordViews = $this->getResultRecords($result);

        foreach ($recordViews as $recordView) {
            if($recordView instanceof RecordViewInterface) {
                $keys = $recordView->keys();
                foreach ($keys as $key) {
                    $record = $recordView->value($key);
                    if($record instanceof Node) {
                        $nodes[] = $record;
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * @param Result $result
     * @return mixed
     */
    public function getSingleItem(Result $result)
    {
        return $this->getRecords($result)->firstRecord()->valueByIndex(0);
    }
}