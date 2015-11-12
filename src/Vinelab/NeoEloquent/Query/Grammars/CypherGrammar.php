<?php

namespace Vinelab\NeoEloquent\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Vinelab\NeoEloquent\Exceptions\InvalidCypherGrammarComponentException;

class CypherGrammar extends Grammar
{
    protected $selectComponents = array(
        'matches',
        'from',
        'with',
        'wheres',
        'unions',
        'columns',
        'orders',
        'offset',
        'limit',
    );

    /**
     * Get the Cypher representation of the query.
     *
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (is_null($query->columns)) {
            $query->columns = array('*');
        }

        return trim($this->concatenate($this->compileComponents($query)));
    }

    /**
     * Compile the components necessary for a select clause.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder
     * @param array|string $specified You may specify a component to compile
     *
     * @return array
     */
    protected function compileComponents(Builder $query, $specified = null)
    {
        $cypher = array();

        $components = array();

        // Setup the components that we need to compile
        if ($specified) {
            // We support passing a string as well
            // by turning it into an array as needed
            // to be $components
            if (!is_array($specified)) {
                $specified = array($specified);
            }

            $components = $specified;
        } else {
            $components = $this->selectComponents;
        }

        foreach ($components as $component) {
            // Compiling return for Neo4j is
            // handled in the compileColumns method
            // in order to keep the convenience provided by Eloquent
            // that deals with collecting and processing the columns
            if ($component == 'return') {
                $component = 'columns';
            }

            $cypher[$component] = $this->compileComponent($query, $components, $component);
        }

        return $cypher;
    }

    /**
     * Compile a single component.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $components
     * @param string                             $component
     *
     * @return string
     */
    protected function compileComponent(Builder $query, $components, $component)
    {
        $cypher = '';

        // Let's make sure this is a proprietary component that we support
        if (!in_array($component, $components)) {
            throw new InvalidCypherGrammarComponentException($component);
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the Cypher.
        if (!is_null($query->$component)) {
            $method = 'compile'.ucfirst($component);

            $cypher = $this->$method($query, $query->$component);
        }

        return $cypher;
    }

    /**
     * Compile the MATCH for a query with relationships.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $matches
     *
     * @return string
     */
    public function compileMatches(Builder $query, $matches)
    {
        if (!is_array($matches) || empty($matches)) {
            return '';
        }

//        $prepared = array();
        $retval = "";

        foreach ($matches as $match) {
            $method = 'prepareMatch'.ucfirst($match['type']);
//            $prepared[] = $this->$method($match);
            $prepared = $this->$method($match);
            $retval .= "MATCH " . $prepared . ' ';
        }

//        return 'MATCH '.implode(', ', $prepared);
        return $retval;
    }

     public function prepareMatchEarly(array $match) {
        $node = $match['node'];
        $labels = $this->prepareLabels($match['labels']);
        $property = $match['property'];

        $q = $match['query'];

        $compwheres = $this->compileWheres($q);

        $matchStatement = '(%s%s) ';

        $retVal =  sprintf($matchStatement, $node, $labels) . $compwheres;

        return $retVal;
    }
    
    /**
     * Prepare a query for MATCH using
     * collected $matches of type Relation.
     *
     * @param array $match
     *
     * @return string
     */
    public function prepareMatchRelation(array $match)
    {
        $parent = $match['parent'];
        $related = $match['related'];
        $property = $match['property'];
        $direction = $match['direction'];
        $relationship = $match['relationship'];

        // Prepare labels for query
        $parentLabels = $this->prepareLabels($parent['labels']);
//        $relatedLabels = $this->prepareLabels($related['labels']);

        // Get the relationship ready for query
        $relationshipLabel = $this->prepareRelation($relationship, $related['node']);

        // We treat node ids differently here in Cypher
        // so we will have to turn it into something like id(node)
        $property = $property == 'id' ? 'id('.$parent['node'].')' : $parent['node'].'.'.$property;

        return '('.$parent['node'].$parentLabels.'), '
            . $this->craftRelation($parent['node'], $relationshipLabel, $related['node'], null, $direction);
    }

    /**
     * Prepare a query for MATCH using
     * collected $matches of Type MorphTo.
     *
     * @param array $match
     *
     * @return string
     */
    public function prepareMatchMorphTo(array $match)
    {
        $parent = $match['parent'];
        $related = $match['related'];
        $property = $match['property'];
        $direction = $match['direction'];

        // Prepare labels and node for query
        $relatedNode = $related['node'];
        $parentLabels = $this->prepareLabels($parent['labels']);

        // We treat node ids differently here in Cypher
        // so we will have to turn it into something like id(node)
        $property = $property == 'id' ? 'id('.$parent['node'].')' : $parent['node'].'.'.$property;

        return '('.$parent['node'].$parentLabels.'), '
                .$this->craftRelation($parent['node'], 'r', $relatedNode, '', $direction);
    }

    /**
     * Craft a Cypher relationship of any type:
     * INCOMING, OUTGOING or BIDIRECTIONAL.
     *
     * examples:
     * ---------
     * OUTGOING
     * [user:User]-[:POSTED]->[post:Post]
     *
     * INCOMING
     * [phone:Phone]<-[:PHONE]-[owner:User]
     *
     * BIDIRECTIONAL
     * [user:User]<-(:FOLLOWS)->[follower:User]
     *
     * @param string $parentNode    The parent Model's node placeholder
     * @param string $relationLabel The label of the relationship i.e. :PHONE
     * @param string $relatedNode   The related Model's node placeholder
     * @param string $relatedLabels Labels of of related Node
     * @param string $direction     Where is it going?
     *
     * @return string
     */
    public function craftRelation($parentNode, $relationLabel, $relatedNode, $relatedLabels, $direction, $bare = false)
    {
        switch (strtolower($direction)) {
            case 'out':
                $relation = '(%s)-[%s]->%s';
            break;

            case 'in':
                $relation = '(%s)<-[%s]-%s';
            break;

            default:
                $relation = '(%s)-[%s]-%s';
            break;
        }

        return ($bare) ? sprintf($relation, $parentNode, $relationLabel, $relatedNode)
            : sprintf($relation, $parentNode, $relationLabel, '('.$relatedNode.$relatedLabels.')');
    }

    /**
     * Compile the "from" portion of the query
     * which in cypher represents the nodes we're MATCHing.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param string                             $labels
     *
     * @return string
     */
    public function compileFrom(Builder $query, $labels)
    {
        // Only compile when no relational matches are specified,
        // mostly used for simple queries.
        if (!empty($query->matches)) {
            return '';
        }

        $labels = $this->prepareLabels($labels);

        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        $labels = ':'.preg_replace('/^:/', '', $labels);

        // now we add the default placeholder for this node
        $labels = $query->modelAsNode().$labels;

        return sprintf('MATCH (%s)', $labels);
    }

    /**
     * Compile the "where" portions of the query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     *
     * @return string
     */
    protected function compileWheres(Builder $query)
    {
        $cypher = array();

        if (is_null($query->wheres)) {
            return '';
        }

        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses Cypher. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        foreach ($query->wheres as $where) {
            $method = "WHERE{$where['type']}";

            $cypher[] = $where['boolean'].' '.$this->$method($query, $where);
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($cypher) > 0) {
            $cypher = implode(' ', $cypher);

            return 'WHERE '.preg_replace('/and |or /', '', $cypher, 1);
        }

        return '';
    }

    /**
     * Compile a basic where clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     *
     * @return string
     */
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where);

        return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
    }

    /**
     * Compiled a WHERE clause with carried identifiers.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $where
     *
     * @return string
     */
    protected function whereCarried(Builder $query, $where)
    {
        return $where['column'].' '.$where['operator'].' '.$where['value'];
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param int                                $limit
     *
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return 'LIMIT '.(int) $limit;
    }

    /**
     * Compile the "SKIP" portions of the query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param int                                $offset
     *
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return 'SKIP '.(int) $offset;
    }

    /**
     * Compile the "RETURN *" portion of the query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $columns
     *
     * @return string
     */
    protected function compileColumns(Builder $query, $properties)
    {
        // When we have an aggregate we will have to return it instead of the plain columns
        // since aggregates for Cypher are not calculated at the beginning of the query like SQL
        // instead we'll have to return in a form such as: RETURN max(user.logins).
        if (!is_null($query->aggregate)) {
            return $this->compileAggregate($query, $query->aggregate);
        }

        $node = $this->query->modelAsNode();

        // We need to make sure that there exists relations so that we return
        // them as well, also there has to be nothing carried in the query
        // to not conflict with them.
        if ($this->hasMatchRelations($query) && empty($query->with)) {
            $relations = $this->getMatchRelations($query);
            $identifiers = [];

            foreach ($relations as $relation) {
                $identifiers[] = $this->getRelationIdentifier($relation['relationship'], $relation['related']['node']);
            }

            $properties = array_merge($properties, $identifiers);
        }

        // In the case where the query has relationships
        // we need to return the requested properties as is
        // since they are considered node placeholders.
        if (!empty($query->matches)) {
            $columns = implode(', ', array_values($properties));
        } else {
            $columns = $this->columnize($properties);
            // when asking for specific properties (not *) we add
            // the node placeholder so that we can get the nodes and
            // the relationships themselves returned
            if (!in_array('*', $properties) && !in_array($node, $properties)) {
                $columns .= ", $node";
            }
        }

        $distinct = ($query->distinct) ? 'DISTINCT ' : '';

        return 'RETURN '.$distinct.$columns;
    }

    /**
     * Compile the "order by" portions of the query.
       * If an order has the 'raw' property then its "column" will
     * not be wrapped.  This is to support ordering by relationship.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $orders
     *
     * @return string
     */
    public function compileOrders(Builder $query, $orders)
    {
//        return 'ORDER BY '.implode(', ', array_map(function ($order) {
//                return $this->wrap($order['column']).' '.mb_strtoupper($order['direction']);
//        }, $orders));
        $retval = null;

        $retval =  'ORDER BY '. implode(', ', array_map(function($order){
            $rv = null;
            if (isset($order['raw']) && $order['raw']) {
                $rv = $order['column'].' '.mb_strtoupper($order['direction']);
            } else {
                $rv = $this->wrap($order['column']).' '.mb_strtoupper($order['direction']);
            }
            return $rv;

         }, $orders));

        return $retval;
    }

    /**
     * Compile a create statement into Cypher.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $values
     *
     * @return string
     */
    public function compileCreate(Builder $query, $values)
    {
        $labels = $this->prepareLabels($query->from);

        $columns = $this->columnsFromValues($values);

        $node = $query->modelAsNode();

        return "CREATE ({$node}{$labels}) SET {$columns} RETURN {$node}";
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $values
     *
     * @return string
     */
    public function compileUpdate(Builder $query, $values)
    {
        $columns = $this->columnsFromValues($values, true);

        // Of course, update queries may also be constrained by where clauses so we'll
        // need to compile the where clauses and attach it to the query so only the
        // intended records are updated by the Cypher statements we generate to run.
        $where = $this->compileWheres($query);

        // We always need the MATCH clause in our Cypher which
        // is the responsibility of compiling the From component.
        $match = $this->compileComponents($query, array('from'));
        $match = $match['from'];

        // When updating we need to return the count of the affected nodes
        // so we trick the Columns compiler into returning that for us.
        $return = $this->compileColumns($query, array('count('.$query->modelAsNode().')'));

        return "$match $where SET $columns $return";
    }

    public function postfixValues(array $values, $updating = false)
    {
        $postfix = $updating ? '_update' : '_create';

        $processed = [];

        foreach ($values as $key => $value) {
            $processed[$key.$postfix] = $value;
        }

        return $processed;
    }

    public function columnsFromValues(array $values, $updating = false)
    {
        $columns = [];
         // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.

        foreach ($values as $key => $value) {
            // Update bindings are differentiated with an _update postfix to make sure the don't clash
            // with query bindings.
            $postfix = $updating ? '_update' : '_create';

            $columns[] = $this->wrap($key).' = '.$this->parameter(array('column' => $key.$postfix));
        }

        return implode(', ', $columns);
    }

    /**
     * Compile a "where in" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     *
     * @return string
     */
    protected function whereIn(Builder $query, $where)
    {
        $values = $this->valufy($where['values']);

        return $this->wrap($where['column']).' IN ['.$values.']';
    }

    /**
     * Compile a delete statement into Cypher.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return string
     */
    public function compileDelete(Builder $query)
    {
        // We always need the MATCH clause in our Cypher which
        // is the responsibility of compiling the From component.
        $match = $this->compileComponents($query, array('from'));
        $match = $match['from'];

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return "$match $where DELETE ".$query->modelAsNode();
    }

    public function compileWith(Builder $query, $with)
    {
        $parts = [];

        if (!empty($with)) {
            foreach ($with as $identifier => $part) {
                $parts[] = (!is_numeric($identifier)) ? "$identifier AS $part" : $part;
            }

            return 'WITH '.implode(', ', $parts);
        }
    }

    /**
     * Compile an insert statement into Cypher.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $values
     *
     * @return string
     */
    public function compileInsert(Builder $query, array $values)
    {
        /*
         *  Essentially we will force every insert to be treated as a batch insert which
         * simply makes creating the Cypher easier for us since we can utilize the same
         * basic routine regardless of an amount of records given to us to insert.
         *
         * We are working on getting a Cypher like this:
         * CREATE (:Wiz {fiz: 'foo', biz: 'boo'}). (:Wiz {fiz: 'morefoo', biz: 'moreboo'})
         */

        if (!is_array($query->from)) {
            $query->from = array($query->from);
        }

        $label = $this->prepareLabels($query->from);

        if (!is_array(reset($values))) {
            $values = array($values);
        }

        // Prepare the values to be sent into the entities factory as
        // ['label' => ':Wiz', 'bindings' => ['fiz' => 'foo', 'biz' => 'boo']]
        $values = array_map(function ($entity) use ($label) {
            return ['label' => $label, 'bindings' => $entity];
        }, $values);
        // We need to build a list of parameter place-holders of values that are bound to the query.
        return 'CREATE '.$this->prepareEntities($values);
    }

    public function compileMatchRelationship(Builder $query, $attributes)
    {
        $startKey = $attributes['start']['id']['key'];
        $startNode = $this->modelAsNode($attributes['start']['label']);
        $startLabel = $this->prepareLabels($attributes['start']['label']);

        if ($startKey === 'id') {
            $startKey = 'id('.$startNode.')';
            $startId = (int) $attributes['start']['id']['value'];
        } else {
            $startKey = $startNode.'.'.$startKey;
            $startId = '"'.addslashes($attributes['start']['id']['value']).'"';
        }

        $startCondition = $startKey.'='.$startId;

        $query = "MATCH ($startNode$startLabel)";

         // we account for no-end relationships.
        if (isset($attributes['end'])) {
            $endKey = $attributes['end']['id']['key'];
            $endNode = 'rel_'.$this->modelAsNode($attributes['label']);
            $endLabel = $this->prepareLabels($attributes['end']['label']);

            if ($attributes['end']['id']['value']) {
                if ($endKey === 'id') {
                    // when it's 'id' it has to be numeric
                    $endKey = 'id('.$endNode.')';
                    $endId = (int) $attributes['end']['id']['value'];
                } else {
                    $endKey = $endNode.'.'.$endKey;
                    $endId = '"'.addslashes($attributes['end']['id']['value']).'"';
                }
            }

            $endCondition = (!empty($endId)) ? $endKey.'='.$endId : '';

            $query .= ", ($endNode$endLabel)";
        }

        $query .= " WHERE $startCondition";

        if (!empty($endCondition)) {
            $query .= " AND $endCondition";
        }

        return $query;
    }

    /**
     * Compile a query that creates a relationship between two given nodes.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $attributes
     *
     * @return string
     */
    public function compileRelationship(Builder $query, $attributes)
    {
        $startNode = $this->modelAsNode($attributes['start']['label']);
        $endNode = 'rel_'.$this->modelAsNode($attributes['label']);

        // support crafting relationships for unknown end nodes,
        // i.e. fetching the relationships of a certain type
        // for a given start node.
        $endLabel = 'r';
        if (isset($attributes['end'])) {
            $endLabel = $attributes['end']['label'];
        }

        $query = $this->craftRelation(
            $startNode,
            'r:'.$attributes['label'],
            '('.$endNode.')',
            $endLabel,
            $attributes['direction'],
            true
        );

        $properties = $attributes['properties'];

        if (!empty($properties)) {
            foreach ($properties as $key => $value) {
                unset($properties[$key]);
                // we do not accept IDs for relations
                if ($key === 'id') {
                    continue;
                }
                $properties[] = 'r.'.$key.' = '.$this->valufy($value);
            }

            $query .= ' SET '.implode(', ', $properties);
        }

        return $query;
    }

    public function compileCreateRelationship(Builder $query, $attributes)
    {
        $match = $this->compileMatchRelationship($query, $attributes);
        $relationQuery = $this->compileRelationship($query, $attributes);
        $query = "$match CREATE UNIQUE $relationQuery";
        $query .= ' RETURN r';

        return $query;
    }

    public function compileDeleteRelationship(Builder $query, $attributes)
    {
        $match = $this->compileMatchRelationship($query, $attributes);
        $relation = $this->compileRelationship($query, $attributes);
        $query = "$match MATCH $relation DELETE r";

        return $query;
    }

    public function compileGetRelationship(Builder $builder, $attributes)
    {
        $match = $this->compileMatchRelationship($builder, $attributes);
        $relation = $this->compileRelationship($builder, $attributes);
        $query = "$match MATCH $relation RETURN r";

        return $query;
    }

    /**
     * Compile a query that creates multiple nodes of multiple model types related all together.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $create
     *
     * @return string
     */
    public function compileCreateWith(Builder $query, $create)
    {
        $model = $create['model'];
        $related = $create['related'];
        $identifier = true; // indicates that we this entity requires an identifier for prepareEntity.

        // Prepare the parent model as a query entity with an identifier to be
        // later used when relating with the rest of the models, something like:
        // (post:`Post` {title: '..', body: '...'})
        $entity = $this->prepareEntity([
            'label' => $model['label'],
            'bindings' => $model['attributes'],
        ], $identifier);

        $parentNode = $this->modelAsNode($model['label']);

        // Prepare the related models as entities for the query.
        $relations = [];
        $attachments = [];
        $createdIdsToReturn = [];
        $attachedIdsToReturn = [];

        foreach ($related as $with) {
            $idKey = $with['id'];
            $label = $with['label'];
            $values = $with['create'];
            $attach = $with['attach'];
            $relation = $with['relation'];

            if (!is_array($values)) {
                $values = (array) $values;
            }

            // Indicate a bare new relation when being crafted so that we distinguish it from relations
            // b/w existing records.
            $bare = true;

            // We need to craft a relationship between the parent model's node identifier
            // and every single relationship record so that we get something like this:
            // (post)-[:PHOTO]->(:Photo {url: '', caption: '..'})
            foreach ($values as $bindings) {
                $identifier = $this->getUniqueLabel($relation['name']);
                // return this identifier as part of the result.
                $createdIdsToReturn[] = $identifier;
                // get a relation cypher.
                $relations[] = $this->craftRelation(
                    $parentNode,
                    ':'.$relation['type'],
                    $this->prepareEntity(compact('label', 'bindings'), $identifier),
                    $this->modelAsNode($label),
                    $relation['direction'],
                    $bare
                );
            }

            // Set up the query parts that are required to attach two nodes.
            if (!empty($attach)) {
                $identifier = $this->getUniqueLabel($relation['name']);
                // return this identifier as part of the result.
                $attachedIdsToReturn[] = $identifier;
                // Now we deal with our attachments so that we create the conditional
                // queries for each relation that we need to attach.
                // $node = $this->modelAsNode($label, $relation['name']);
                $nodeLabel = $this->prepareLabels($label);

                // An attachment query is a combination of MATCH, WHERE and CREATE where
                // we MATCH the nodes that we need to attach, set the conditions
                // on the records that we need to attach with WHERE and then
                // CREATE these relationships.
                $attachments['matches'][] = "({$identifier}{$nodeLabel})";

                if($idKey === 'id') {
                    // Native Neo4j IDs are treated differently
                    $attachments['wheres'][] = "id($identifier) IN [".implode(', ', $attach).']';
                } else {
                    $attachments['wheres'][] = "$identifier.$idKey IN [\"".implode('", "', $attach).'"]';
                }

                $attachments['relations'][] = $this->craftRelation(
                    $parentNode,
                    ':'.$relation['type'],
                    "($identifier)",
                    $nodeLabel,
                    $relation['direction'],
                    $bare
                );
            }
        }
        // Return the Cypher representation of the query that would look something like:
        // CREATE (post:`Post` {title: '..', body: '..'})
        $cypher = 'CREATE '.$entity;
        // Then we add the records that we need to create as such:
        // (post)-[:PHOTO]->(:`Photo` {url: ''}), (post)-[:VIDEO]->(:`Video` {title: '...'})
        if (!empty($relations)) {
            $cypher .= ', '.implode(', ', $relations);
        }
        // Now we add the attaching Cypher
        if (!empty($attachments)) {
            // Bring the parent node along with us to be used in the query further.
            $cypher .= " WITH $parentNode";

            if (!empty($createdIdsToReturn)) {
                $cypher  .= ', '.implode(', ', $createdIdsToReturn);
            }

            // MATCH the related nodes that we are attaching.
            $cypher .= ' MATCH '.implode(', ', $attachments['matches']);
            // Set the WHERE conditions for the heart of the query.
            $cypher .= ' WHERE '.implode(' AND ', $attachments['wheres']);
            // CREATE the relationships between matched nodes
            $cypher .= ' CREATE UNIQUE'.implode(', ', $attachments['relations']);
        }

        $cypher .= " RETURN $parentNode, ".implode(', ', array_merge($createdIdsToReturn, $attachedIdsToReturn));

        return $cypher;
    }

    public function compileAggregate(Builder $query, $aggregate)
    {
        $distinct = null;
        $function = $aggregate['function'];
        // When calling for the distinct count we'll set the distinct flag and ask for the count function.
        if ($function == 'countDistinct') {
            $function = 'count';
            $distinct = 'DISTINCT ';
        }

//        $node = $this->modelAsNode($aggregate['label']);
        $node  = $this->modelAsNode($query->from); // tomb - now gets labels from query not aggregate

        // We need to format the columns to be in the form of n.property unless it is a *.
        $columns = implode(', ', array_map(function ($column) use ($node) {
            return $column == '*' ? $column : "$node.$column";
        }, $aggregate['columns']));

//        if (!is_null($aggregate['percentile'])) {
        if ( isset($aggregate['percentile']) && ! is_null($aggregate['percentile'])) {            
            $percentile = $aggregate['percentile'];

            return "RETURN $function($columns, $percentile)";
        }

        return "RETURN $function($distinct$columns)";
    }

    /**
     * Compile an statement to add or drop node labels.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $labels    labels as string like :label1:label2 etc
     * @param array                              $operation type of operation 'add' or 'drop'
     *
     * @return string
     */
    public function compileUpdateLabels(Builder $query, $labels, $operation = 'add')
    {
        if (trim(strtolower($operation)) == 'add') {
            $updateType = 'SET';
        } else {
            $updateType = 'REMOVE';
        }
        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.

        $labels = $query->modelAsNode().$this->prepareLabels($labels);

        // Of course, update queries may also be constrained by where clauses so we'll
        // need to compile the where clauses and attach it to the query so only the
        // intended records are updated by the Cypher statements we generate to run.
        $where = $this->compileWheres($query);

        // We always need the MATCH clause in our Cypher which
        // is the responsibility of compiling the From component.
        $match = $this->compileComponents($query, array('from'));
        $match = $match['from'];

        return "$match $where $updateType $labels RETURN ".$query->modelAsNode();
    }
}
