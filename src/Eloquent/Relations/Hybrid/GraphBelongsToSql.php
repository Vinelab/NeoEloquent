<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations\Hybrid;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraphBelongsToSql extends BelongsTo
{
    public function addEagerConstraints(array $models)
    {
        $models = $this->getFirstsOfArray($models)->all();
        parent::addEagerConstraints($models);
    }

    public function initRelation(array $models, $relation)
    {
        $sub = $this->getFirstsOfArray($models)->all();

        foreach ($sub as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;
        $owner = $this->ownerKey;

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->getAttribute($owner)] = $result;
        }

        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
        $sub = $this->getFirstsOfArray($models)->all();
        foreach ($sub as $model) {
            if (isset($dictionary[$model->{$foreign}])) {
                $model->setRelation($relation, $dictionary[$model->{$foreign}]);
            }
        }

        return $models;
    }


    private function getFirstsOfArray(array $models)
    {
        return collect($models)->map(function ($item) {
            if (is_object($item))
                return $item;

            $first = array_first($item);
            if (is_array($first)) {
                return $this->getFirstsOfArray($first);
            } else
                return $first;
        })->values();
    }
}
