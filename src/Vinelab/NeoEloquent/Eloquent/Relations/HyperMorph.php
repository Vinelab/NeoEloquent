<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class HyperMorph extends BelongsToMany {

    /**
     * The morph Model instance
     * representing the 3rd Node of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Model
     */
    protected $morph;

    /**
     * The morph relation type name representing the relationship
     * name b/w the related model and the morph model.
     *
     * @var string
     */
    protected $morphType;

    /**
     * Create a new HyperMorph relationship.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param Vinelab\NeoEloquent\Eloquent\Model   $morph
     * @param string  $type
     * @param string  $morphType
     * @param string  $key
     * @param string  $relation
     */
    public function __construct(Builder $query, Model $parent, $morph, $type, $morphType, $key, $relation)
    {
        $this->morph = $morph;
        $this->morphType = $morphType;

        parent::__construct($query, $parent, $type, $key, $relation);
    }

    public function edge(Model $model = null)
    {
        return $this->finder->hyperFirst($this->parent, $model, $this->morph, $this->type, $this->morphType);
    }

    public function getEdge(EloquentModel $model = null, $properties = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->related;

        return new HyperEdge($this->query, $this->parent, $this->type, $model, $this->morphType, $this->morph, $properties);
    }

}
