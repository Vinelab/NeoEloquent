<?php namespace Vinelab\NeoEloquent\Eloquent\Edges;

class EdgeIn extends Relation {

    public function initRelation()
    {
        // make nodes
        $start = $this->asNode($this->related);
        $end = $this->asNode($this->parent);

        // setup relationship
        $this->relation = $this->client->makeRelationship()
            ->setType($this->type)
            ->setStartNode($start)
            ->setEndNode($end);
    }

}
