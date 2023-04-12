<?php

namespace Vinelab\NeoEloquent;

use Vinelab\NeoEloquent\Query\CypherGrammar;
use WikibaseSolutions\CypherDSL\Parameter;

trait ManagesDSLContext
{
    /**
     * @param callable(DSLContext): string $compilation
     *
     * @return string
     */
    protected function witCachedParams(callable $compilation): string
    {
        $context = new DSLContext();

        $tbr = $compilation($context);

        CypherGrammar::cacheContext($tbr, $context);

        return $tbr;
    }

    public static function cacheContext(string $query, DSLContext $context): void
    {
        CypherGrammar::$contextCache[$query] = $context;
    }

    public static function getBoundParameters(string $query): array
    {
        return (CypherGrammar::$contextCache[$query] ?? null)?->getParameters() ?? [];
    }
}