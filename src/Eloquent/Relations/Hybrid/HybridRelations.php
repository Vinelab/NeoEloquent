<?php namespace Vinelab\NeoEloquent\Eloquent\Relations\Hybrid;

use Illuminate\Database\Eloquent\Model;

trait HybridRelations
{
    public function hasOneHybrid($related, $foreignKey = null, $localKey = null)
    {
        //TO make relation from non-relational to relational
        if (!is_subclass_of($related, 'Vinelab\NeoEloquent\Eloquent\Model')) {
            return Model::hasOne($related, $foreignKey, $localKey);
        }

        //TO make relation from relational to non-relational
        $relation = $this->guessBelongsToRelation();
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();
        $instance = $this->newRelatedInstance($related);

        return new HasOne($instance->newQuery(), $this, $foreignKey, $localKey, $relation);
    }

    public function belongsToHybrid($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();
        $relation = $relation ?: $this->guessBelongsToRelation();

        //TO make relation from non-relational to relational
        if (!is_subclass_of($related, 'Vinelab\NeoEloquent\Eloquent\Model')) {
            return new GraphBelongsToSql($instance->newQuery(), $this, $foreignKey, $ownerKey, $relation);
        }

        //TO make relation from relational to non-relational
        return new SqlBelongsToGraph($instance->newQuery(), $this, $foreignKey, $ownerKey, $relation);
    }
}