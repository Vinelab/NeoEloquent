<?php namespace Vinelab\NeoEloquent;

use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar;

class NeoEloquentServiceProvider extends ServiceProvider {

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
        'Migration'
    );

    /**
    * Bootstrap the application events.
    *
    * @return void
    */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['db']->extend('neo4j', function($config)
        {
            $conn = new Connection($config);
            $conn->setSchemaGrammar(new CypherGrammar);
            return $conn;
        });

        $this->app->resolving(function($app){
            if (class_exists('Illuminate\Foundation\AliasLoader')) {
                $loader = \Illuminate\Foundation\AliasLoader::getInstance();
                $loader->alias('NeoEloquent', 'Vinelab\NeoEloquent\Eloquent\Model');
                $loader->alias('Neo4jSchema', 'Vinelab\NeoEloquent\Facade\Neo4jSchema');
            }
        });


        $this->registerComponents();
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
     *
     * @return void
     */
    protected function registerMigration()
    {
        $this->app->register('Vinelab\NeoEloquent\MigrationServiceProvider');
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
