<?php

namespace Vinelab\NeoEloquent;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\ConnectionResolver;
use Throwable;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\NeoEloquentFactory;
use Illuminate\Support\ServiceProvider;

use Faker\Generator as FakerGenerator;
use function array_filter;

class NeoEloquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $resolver = new ConnectionResolver();
        $factory = ConnectionFactory::default();
        /** @var Repository $config */
        $config = $this->app->get('config');
        $connections = $config->get('database.connections', []);
        $connections = array_filter($connections, static fn (array $x) => ($x['driver'] ?? '') === 'neo4j');

        foreach ($connections as $name => $connection) {
            $resolver->addConnection($name, $factory->make($connection));
        }

        if ($config->has('database.default')) {
            $resolver->setDefaultConnection($config->get('database.default'));
        }

        Model::setConnectionResolver($resolver);
    }

    /**
     * Register the service provider.
     *
     * @throws Throwable
     */
    public function register(): void
    {
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
