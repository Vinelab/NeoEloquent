<?php

namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Closure;
use Illuminate\Database\Query\JoinClause;

class RelationshipJoin extends JoinClause
{
    /**
     * Add an "on" clause to the join.
     *
     * On clauses can be chained, e.g.
     *
     *  $join->on('contacts.user_id', '=', 'users.id')
     *       ->on('contacts.info_id', '=', 'info.id')
     *
     * will produce the following SQL:
     *
     * on `contacts`.`user_id` = `users`.`id` and `contacts`.`info_id` = `info`.`id`
     *
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  \Illuminate\Database\Query\Expression|string|null  $second
     * @param  string  $boolean
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function on($first, $operator = null, $second = null, $boolean = 'and')
    {
        if ($first instanceof Closure) {
            return $this->whereNested($first, $boolean);
        }

        return $this->whereColumn($first, $operator, $second, $boolean);
    }

    /**
     * Add an "or on" clause to the join.
     *
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  \Illuminate\Database\Query\Expression|string|null  $second
     * @return \Illuminate\Database\Query\JoinClause
     */
    public function orOn($first, $operator = null, $second = null)
    {
        return $this->on($first, $operator, $second, 'or');
    }
}