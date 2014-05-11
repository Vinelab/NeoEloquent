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

        // set the database configuration for the environment
        $app['config']->set('database', $config);
    }
}
