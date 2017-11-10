<?php

namespace Vinelab\NeoEloquent\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Concerns\QueriesRelationships as QR;
/**
 * Created by PhpStorm.
 * User: tomahock
 * Date: 09/02/2017
 * Time: 17:21
 */
trait QueriesRelationships
{
    use QR;

    /**
     * Add a relationship query condition.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int     $count
     * @param  string  $boolean
     * @param  \Closure  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', \Closure $callback = null)
    {
        $relation = $this->getRelationWithoutConstraints($relation);

        $query = $relation->getRelated()->newQuery();
        // This will make sure that any query we add here will consider the related
        // model as our reference Node.
        $this->getQuery()->from = $query->getModel()->getTable();

        if ($callback) call_user_func($callback, $query);

        /**
         * In graph we do not need to act on the count of the relationships when dealing
         * with a whereHas() since the database will not return the result unless a relationship
         * exists between two nodes.
         */
        $prefix = $relation->getRelatedNode();

        if ( ! $callback)
        {
            /**
             * The Cypher we're trying to build here would look like this:
             *
             * MATCH (post:`Post`)-[r:COMMENT]-(comments:`Comment`)
             * WITH count(comments) AS comments_count, post
             * WHERE comments_count >= 10
             * RETURN post;
             *
             * Which is the result of Post::has('comments', '>=', 10)->get();
             */
            $countPart = $prefix .'_count';
            $this->carry([$relation->getParentNode(), "count($prefix)" => $countPart]);
            $this->whereCarried($countPart, $operator, $count);
        }

        $parentNode = $relation->getParentNode();
        $relatedNode = $relation->getRelatedNode();
        // Tell the query to select our parent node only.
        $this->select($parentNode);
        // Set the relationship match clause.
        $method = $this->getMatchMethodName($relation);

        $this->$method($relation->getParent(),
            $relation->getRelated(),
            $relatedNode,
            $relation->getForeignKeyName(),
            $relation->getLocalKey(),
            $relation->getParentLocalKeyValue());

        // Prefix all the columns with the relation's node placeholder in the query
        // and merge the queries that needs to be merged.
        $this->prefixAndMerge($query, $prefix);

        /**
         * After that we've done everything we need with the Has() and related we need
         * to reset the query for the grammar so that whenever we continu querying we make
         * sure that we're using the correct grammar. i.e.
         *
         * $user->whereHas('roles', function(){})->where('id', $user->id)->first();
         */
        $grammar = $this->getQuery()->getGrammar();
        $grammar->setQuery($this->getQuery());
        $this->getQuery()->from = $this->getModel()->getTable();

        return $this;
    }
}