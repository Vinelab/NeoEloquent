<?php

namespace Vinelab\NeoEloquent;


use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\NeoEloquentFactory;
use Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Connection as NeoEloquentConnection;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

use Faker\Generator as FakerGenerator;

class NeoEloquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app->make(Dispatcher::class));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app['db']->extend('neo4j', function ($config) {
            $this->config = $config;
            $conn = new ConnectionAdapter($config);
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
    
        $this->app->singleton(NeoEloquentFactory::class, function ($app) {
            return NeoEloquentFactory::construct(
                $app->make(FakerGenerator::class), $this->app->databasePath('factories')
            );
        });

        if ($this->app->runningInConsole()) {
            $this->app->register(MigrationServiceProvider::class);
        }
    }
}
