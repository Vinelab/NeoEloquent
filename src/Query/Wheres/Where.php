<?php

namespace Vinelab\NeoEloquent\Query\Wheres;

use Illuminate\Database\Query\Builder;
use Vinelab\NeoEloquent\DSLContext;
use WikibaseSolutions\CypherDSL\Clauses\CallClause;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;

class Where
{
    /** @var CallClause[] */
    private array $calls = [];
    /** @var BooleanType|null */
    private ?BooleanType $expression = null;

    public function decorate(Query $query): void
    {
        if ($this->expression) {
            foreach ($this->calls as $call) {
                $query->addClause($call);
            }

            $query->where($this->expression);
        }
    }

    public function addCall(CallClause $clause): void
    {
        $this->calls[] = $clause;
    }

    private function addExpression(BooleanType $expression, bool $and = true, bool $insertParentheses = false): void
    {
        if ($this->expression === null) {
            $this->expression = $expression;
        } else if ($and) {
            $this->expression->and($expression, $insertParentheses);
        } else {
            $this->expression->or($expression, $insertParentheses);
        }
    }

    public function addExpressionFromWhere(BooleanType $expression, array $where, bool $insertParentheses = false): void
    {
        $this->addExpression($expression, strtolower($where['boolean']) === 'and', $insertParentheses);
    }
}