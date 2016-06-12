<?php

namespace Vinelab\NeoEloquent\Frameworks\Laravel;

use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Vinelab\NeoEloquent\Events\Dispatcher;
use Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar;

class NeoEloquentServiceProvider52 extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Components to register on the provider.
     *
     * @var array
     */
    protected $components = array(
        'Migration',
    );

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app->make(Dispatcher::class));
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['db']->extend('neo4j', function ($config) {
            $this->config = $config;
            $conn = new Connection($config);
            $conn->setSchemaGrammar(new CypherGrammar());

            return $conn;
        });

         $this->app->bind('neoeloquent.connection', function() {
             // $config is set by the previous binding,
             // so that we get the correct configuration
             // set by the user.
             $conn = new NeoEloquentConnection($this->config);
             $conn->setSchemaGrammar(new CypherGrammar());

             return $conn;
         });

        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('NeoEloquent', 'Vinelab\NeoEloquent\Eloquent\Model');
            $loader->alias('Neo4jSchema', 'Vinelab\NeoEloquent\Facade\Neo4jSchema');
        });

        $this->registerComponents();
    }

    protected function registerNeoEloquentConnection($app, $config)
    {
        $app->bind('neoeloquent.connection', function() use($config) {
            $conn = new NeoEloquentConnection($config);
            $conn->setSchemaGrammar(new CypherGrammar());

            return $conn;
        });
    }

    /**
     * Register components on the provider.
     *
     * @var array
     */
    protected function registerComponents()
    {
        foreach ($this->components as $component) {
            $this->{'register'.$component}();
        }
    }

    /**
     * Register the migration service provider.
     */
    protected function registerMigration()
    {
        $this->app->register(MigrationServiceProvider52::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
        );
    }
}
