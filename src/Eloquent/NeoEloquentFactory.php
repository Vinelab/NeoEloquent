<?php namespace Vinelab\NeoEloquent\Eloquent;

use Symfony\Component\Finder\Finder;
use Vinelab\NeoEloquent\Eloquent\NeoFactoryBuilder;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

/**
 * Class NeoEloquentFactory
 *
 * @author Ivan Hunko <ivan@vinelab.com>
 */
class NeoEloquentFactory extends EloquentFactory
{
    /**
     * Create a builder for the given model.
     *
     * @param $class
     * @param  string  $name
     * @return \Vinelab\NeoEloquent\Eloquent\NeoFactoryBuilder
     */
    public function of($class, $name = 'default')
    {
        return new NeoFactoryBuilder($class, $name, $this->definitions, $this->states, $this->faker);
    }
}
