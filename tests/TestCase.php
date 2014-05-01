<?php namespace Vinelab\NeoEloquent\Tests;

class TestCase extends \Orchestra\Testbench\TestCase {

    protected function getPackageProviders()
    {
        return array('Vinelab\NeoEloquent\NeoEloquentServiceProvider');
    }

    protected function getEnvironmentSetup($app)
    {
        // load custom configuration file
        $config = require 'config/database.php';

        // make neo4j the default datbase
        $app['config']->set('database.default', 'neo4j');

        // setup connection parameters
        $app['config']->set('database.connections.neo4j', $config['connections']['neo4j']);
    }
}
