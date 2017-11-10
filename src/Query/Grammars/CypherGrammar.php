<?php namespace Vinelab\NeoEloquent\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Vinelab\NeoEloquent\Exceptions\InvalidCypherGrammarComponentException;

class CypherGrammar extends Grammar {

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
        if (is_null($query->columns)) $query->columns = array('*');

        return trim($this->concatenate($this->compileComponents($query)));
    }


    /**
     * Compile the components necessary for a select clause.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder
     * @param  array|string $specified You may specify a component to compile
     * @return array
     */
    protected function compileComponents(Builder $query, $specified = null)
    {
        $cypher = array();

        $components = array();

        // Setup the components that we need to compile
        if ($specified)
        {
            // We support passing a string as well
            // by turning it into an array as needed
            // to be $components
            if ( ! is_array($specified))
            {
                $specified = array($specified);
            }

            $components = $specified;

        } else
        {
            $components = $this->selectComponents;
        }

        foreach ($components as $component)
        {
            // Compiling return for Neo4j is
            // handled in the compileColumns method
            // in order to keep the convenience provided by Eloquent
            // that deals with collecting and processing the columns
            if ($component == 'return') $component = 'columns';

            $cypher[$component] = $this->compileComponent($query, $components, $component);
        }

        return $cypher;
    }

    /**
     * Compile a single component.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  array $components
     * @param  string $component
     * @return string
     */
    protected function compileComponent(Builder $query, $components, $component)
    {
        $cypher = '';

        // Let's make sure this is a proprietary component that we support
        if ( ! in_array($component, $components))
        {
            throw new InvalidCypherGrammarComponentException($component);
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the Cypher.
        if ( ! is_null($query->$component))
        {
            $method = 'compile'.ucfirst($component);

            $cypher = $this->$method($query, $query->$component);
        }

        return $cypher;
    }

    /**
     * Compile the MATCH for a query with relationships.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  array  $matches
     * @return string
     */
    public function compileMatches(Builder $query, $matches)
    {
        if ( ! is_array($matches) || empty($matches)) return '';

        $prepared = array();

        foreach ($matches as $match)
        {
            $method = 'prepareMatch'. ucfirst($match['type']);
            $prepared[] = $this->$method($match);
        }

        return "MATCH " . implode(', ', $prepared);
    }

    /**
     * Prepare a query for MATCH using
     * collected $matches of type Relation
     *
     * @param  array $match
     * @return string
     */
    public function prepareMatchRelation(array $match)
    {
        $parent        = $match['parent'];
        $related       = $match['related'];
        $property      = $match['property'];
        $direction     = $match['direction'];
        $relationship  = $match['relationship'];

        // Prepare labels for query
        $parentLabels  = $this->prepareLabels($parent['labels']);
        $relatedLabels = $this->prepareLabels($related['labels']);

        // Get the relationship ready for query
        $relationshipLabel = $this->prepareRelation($relationship, $related['node']);

        // We treat node ids differently here in Cypher
        // so we will have to turn it into something like id(node)
        $property = $property == 'id' ? 'id('. $parent['node'] .')' : $parent['node'] .'.'. $property;

        return '('. $parent['node'] . $parentLabels .'), '
                . $this->craftRelation($parent['node'], $relationshipLabel, $related['node'], $relatedLabels, $direction);
    }

    /**
     * Prepare a query for MATCH using
     * collected $matches of Type MorphTo
     *
     * @param  array $match
     * @return string
     */
    public function prepareMatchMorphTo(array $match)
    {
        $parent        = $match['parent'];
        $related       = $match['related'];
        $property      = $match['property'];
        $direction     = $match['direction'];

        // Prepare labels and node for query
        $relatedNode = $related['node'];
        $parentLabels  = $this->prepareLabels($parent['labels']);

        // We treat node ids differently here in Cypher
        // so we will have to turn it into something like id(node)
        $property = $property == 'id' ? 'id('. $parent['node'] .')' : $parent['node'] .'.'. $property;

        return '('. $parent['node'] . $parentLabels .'), '
                . $this->craftRelation($parent['node'], 'r', $relatedNode, '', $direction);
    }

    /**
     * Craft a Cypher relationship of any type:
     * INCOMING, OUTGOING or BIDIRECTIONAL
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
     * @param  string $parentNode    The parent Model's node placeholder
     * @param  string $relationLabel The label of the relationship i.e. :PHONE
     * @param  string $relatedNode   The related Model's node placeholder
     * @param  string $relatedLabels Labels of of related Node
     * @param  string $direction     Where is it going?
     * @return string
     */
    public function craftRelation($parentNode, $relationLabel, $relatedNode, $relatedLabels, $direction, $bare = false)
    {
        switch($direction)
        {
            case 'out':
            default:
                $relation = '(%s)-[%s]->%s';
            break;

            case 'in':
                $relation = '(%s)<-[%s]-%s';
            break;

            case 'in-out':
                $relation = '(%s)<-[%s]->%s';
            break;
        }

        return ($bare) ? sprintf($relation, $parentNode, $relationLabel, $relatedNode)
            : sprintf($relation, $parentNode, $relationLabel, '('. $relatedNode.$relatedLabels .')');
    }


    /**
     * Compile the "from" portion of the query
     * which in cypher represents the nodes we're MATCHing
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  string  $labels
     * @return string
     */
    public function compileFrom(Builder $query, $labels)
    {
        // Only compile when no relational matches are specified,
        // mostly used for simple queries.
        if ( ! empty($query->matches)) return '';

        // first we will check whether we need
        // to reformat the labels from an array
        if (is_array($labels))
        {
            $labels = $this->prepareLabels($labels);
        }

        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        $labels = ':' . preg_replace('/^:/', '', $labels);

        // now we add the default placeholder for this node
        $labels = $query->modelAsNode() . $labels;

        return sprintf("MATCH (%s)", $labels);
    }

    
    /**
     * Compile a "where not in" clause.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (empty($where['values'])) {
            return '1 = 1';
        }

        $values = $this->parameterize($where['values']);
        $values = str_replace(['{','}'], "'", $values);
        return 'not '. $this->wrap($where['column']).' in ['.$values.']';
    }

        /**
     * Compile the "where" portions of the query.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @return string
     */
    protected function compileWheres(Builder $query)
    {
        $cypher = array();

        if (is_null($query->wheres)) return '';

        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses Cypher. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        foreach ($query->wheres as $where)
        {
            $method = "WHERE{$where['type']}";

            $cypher[] = $where['boolean'].' '.$this->$method($query, $where);
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($cypher) > 0)
        {
            $cypher = implode(' ', $cypher);

            return 'WHERE '.preg_replace('/and |or /', '', $cypher, 1);
        }

        return '';
    }

    /**
     * Compile a basic where clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
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
     * @param  \Vinelab\NeoEloquent\Query\Builder $query
     * @param  array  $where
     * @return string
     */
    protected function whereCarried(Builder $query, $where)
    {
        return $where['column'] .' '. $where['operator']. ' '.$where['value'];
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  int  $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return 'LIMIT '.(int) $limit;
    }

    /**
     * Compile the "SKIP" portions of the query.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  int  $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return 'SKIP '.(int) $offset;
    }

    /**
     * Compile the "RETURN *" portion of the query.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  array  $columns
     * @return string
     */
    protected function compileColumns(Builder $query, $properties)
    {
        // When we have an aggregate we will have to return it instead of the plain columns
        // since aggregates for Cypher are not calculated at the beginning of the query like SQL
        // instead we'll have to return in a form such as: RETURN max(user.logins).
        if ( ! is_null($query->aggregate)) return $this->compileAggregate($query, $query->aggregate);

        // In the case where the query has relationships
        // we need to return the requested properties as is
        // since they are considered node placeholders.
        if ( ! empty($query->matches))
        {
            $properties = implode(', ', array_values($properties));
        } else
        {
            $properties = $this->columnize($properties);
        }

        $distinct = ($query->distinct) ? 'DISTINCT ' : '';

        return 'RETURN ' . $distinct . $properties;
    }

    /**
     * Compile the "order by" portions of the query.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder $query
     * @param  array  $orders
     * @return string
     */
    public function compileOrders(Builder $query, $orders)
    {
        return 'ORDER BY '. implode(', ', array_map(function($order){
                return $this->wrap($order['column']).' '.mb_strtoupper($order['direction']);
        }, $orders));
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileUpdate(Builder $query, $values)
    {
        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.

        foreach ($values as $key => $value)
        {
            // Update bindings are differentiated with an _update postfix to make sure the don't clash
            // with query bindings.
            $columns[] = $this->wrap($key) . ' = ' . $this->parameter(array('column' => $key .'_update'));
        }

        $columns = implode(', ', $columns);

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
        $return = $this->compileColumns($query, array('count('. $query->modelAsNode() .')'));

        return "$match $where SET $columns $return";
    }

    /**
     * Compile a "where in" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
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
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileDelete(Builder $query)
    {
        // We always need the MATCH clause in our Cypher which
        // is the responsibility of compiling the From component.
        $match = $this->compileComponents($query, array('from'));
        $match = $match['from'];

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

       
        return "$match $where OPTIONAL $match-[r]-()  $where DELETE  " . $query->modelAsNode().",r";

    }

    public function compileWith(Builder $query, $with)
    {
        $parts = [];

        if ( ! empty($with))
        {
            foreach ($with as $identifier => $part)
            {
                $parts[] = ( ! is_numeric($identifier)) ? "$identifier AS $part" : $part;
            }

            return 'WITH '. implode(', ', $parts);
        }
    }

    /**
     * Compile an insert statement into Cypher.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values)
    {
        /**
         *  Essentially we will force every insert to be treated as a batch insert which
         * simply makes creating the Cypher easier for us since we can utilize the same
         * basic routine regardless of an amount of records given to us to insert.
         *
         * We are working on getting a Cypher like this:
         * CREATE (:Wiz {fiz: 'foo', biz: 'boo'}). (:Wiz {fiz: 'morefoo', biz: 'moreboo'})
         */

        if ( ! is_array($query->from))
        {
            $query->from = array($query->from);
        }

        $label = $this->prepareLabels($query->from);

        if ( ! is_array(reset($values)))
        {
            $values = array($values);
        }

        // Prepare the values to be sent into the entities factory as
        // ['label' => ':Wiz', 'bindings' => ['fiz' => 'foo', 'biz' => 'boo']]
        $values = array_map(function($entity) use($label)
        {
            return ['label' => $label, 'bindings' => $entity];
        }, $values);
        // We need to build a list of parameter place-holders of values that are bound to the query.
        return "CREATE ". $this->prepareEntities($values);
    }

    /**
     * Compile a query that creates multiple nodes of multiple model types related all together.
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder $query
     * @param  array  $create
     * @return string
     */
    public function compileCreateWith(Builder $query, $create)
    {
        $model   = $create['model'];
        $related = $create['related'];
        $identifier = true; // indicates that we this entity requires an identifier for prepareEntity.

        // Prepare the parent model as a query entity with an identifier to be
        // later used when relating with the rest of the models, something like:
        // (post:`Post` {title: '..', body: '...'})
        $entity = $this->prepareEntity([
            'label'    => $model['label'],
            'bindings' => $model['attributes']
        ], $identifier);

        $parentNode = $this->modelAsNode($model['label']);

        // Prepare the related models as entities for the query.
        $relations = [];
        $attachments = [];
        $createdIdsToReturn = [];
        $attachedIdsToReturn = [];

        foreach ($related as $with)
        {
            $label    = $with['label'];
            $values   = $with['create'];
            $attach   = $with['attach'];
            $relation = $with['relation'];

            if ( ! is_array($values)) $values = (array) $values;

            // Indicate a bare new relation when being crafted so that we distinguish it from relations
            // b/w existing records.
            $bare = true;

            // We need to craft a relationship between the parent model's node identifier
            // and every single relationship record so that we get something like this:
            // (post)-[:PHOTO]->(:Photo {url: '', caption: '..'})
            foreach ($values as $bindings)
            {
                $identifier = $this->getUniqueLabel($relation['name']);
                // return this identifier as part of the result.
                $createdIdsToReturn[] = $identifier;
                // get a relation cypher.
                $relations[] = $this->craftRelation(
                    $parentNode,
                    ':'. $relation['type'],
                    $this->prepareEntity(compact('label', 'bindings'), $identifier),
                    $this->modelAsNode($label),
                    $relation['direction'],
                    $bare
                );
            }

            // Set up the query parts that are required to attach two nodes.
            if ( ! empty($attach))
            {
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
                $attachments['wheres'][]  = "id($identifier) IN [". implode(', ', $attach) .']';
                $attachments['relations'][] = $this->craftRelation(
                    $parentNode,
                    ':'. $relation['type'],
                    "($identifier)",
                    $nodeLabel,
                    $relation['direction'],
                    $bare
                );
            }
        }
        // Return the Cypher representation of the query that would look something like:
        // CREATE (post:`Post` {title: '..', body: '..'})
        $cypher = 'CREATE '. $entity;
        // Then we add the records that we need to create as such:
        // (post)-[:PHOTO]->(:`Photo` {url: ''}), (post)-[:VIDEO]->(:`Video` {title: '...'})
        if ( ! empty($relations)) $cypher .= ', '. implode(', ', $relations);
        // Now we add the attaching Cypher
        if ( ! empty($attachments))
        {
            // Bring the parent node along with us to be used in the query further.
            $cypher .= " WITH $parentNode";

            if (! empty($createdIdsToReturn))
            {
                $cypher  .= ', '.implode(', ', $createdIdsToReturn);
            }

            // MATCH the related nodes that we are attaching.
            $cypher .= ' MATCH '. implode(', ', $attachments['matches']);
            // Set the WHERE conditions for the heart of the query.
            $cypher .= ' WHERE '. implode(' AND ', $attachments['wheres']);
            // CREATE the relationships between matched nodes
            $cypher .= ' CREATE UNIQUE'. implode(', ', $attachments['relations']);
        }

        $cypher .= " RETURN $parentNode, ".implode(', ', array_merge($createdIdsToReturn, $attachedIdsToReturn));

        return $cypher;
    }

    public function compileAggregate(Builder $query, $aggregate)
    {
        $distinct = null;
        $function = $aggregate['function'];
        // When calling for the distinct count we'll set the distinct flag and ask for the count function.
        if ($function == 'countDistinct')
        {
            $function = 'count';
            $distinct = 'DISTINCT ';
        }

        $node  = $this->modelAsNode($query->from);

        // We need to format the columns to be in the form of n.property unless it is a *.
        $columns  = implode(', ', array_map(function($column) use($node) {
            return $column == '*' ? $column : "$node.$column";
        }, $aggregate['columns']));

        if ( isset($aggregate['percentile']) && ! is_null($aggregate['percentile']))
        {
            $percentile = $aggregate['percentile'];
            return "RETURN $function($columns, $percentile)";
        }

        return "RETURN $function($distinct$columns)";
    }

    /**
     * Compile an statement to add or drop node labels
     *
     * @param  \Vinelab\NeoEloquent\Query\Builder  $query
     * @param  array $labels labels as string like :label1:label2 etc
	 * @param  array $operation type of operation 'add' or 'drop'
     * @return string
     */
    public function compileUpdateLabels(Builder $query, $labels, $operation = 'add' )
    {
        if(trim(strtolower($operation)) == 'add')
        {
            $updateType = 'SET';
        } else
        {
            $updateType = 'REMOVE';
        }
        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.

        $labels = $query->modelAsNode().$this->prepareLabels( $labels );

        // Of course, update queries may also be constrained by where clauses so we'll
        // need to compile the where clauses and attach it to the query so only the
        // intended records are updated by the Cypher statements we generate to run.
        $where = $this->compileWheres($query);

        // We always need the MATCH clause in our Cypher which
        // is the responsibility of compiling the From component.
        $match = $this->compileComponents($query, array('from'));
        $match = $match['from'];

        return "$match $where $updateType $labels ";
    }

}
