<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use function is_null;

class HasMany extends HasOneOrMany
{
    /**
     * Get the results of the relationship.
     *
     * @return Collection|Builder[]
     */
    public function getResults()
    {
        return ! is_null($this->getParentKey())
            ? $this->query->get()
            : $this->related->newCollection();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param Collection $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation): array
    {
        return $this->matchMany($models, $results, $relation);
    }
}
