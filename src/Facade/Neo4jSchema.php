<?php namespace Vinelab\NeoEloquent\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vinelab\NeoEloquent\Schema\Builder
 */
class Neo4jSchema extends Facade {

    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string  $name
     * @return \Vinelab\NeoEloquent\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['db']->connection()->getSchemaBuilder();
    }

}
