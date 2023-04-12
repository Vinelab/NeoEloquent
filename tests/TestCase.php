<?php

namespace Vinelab\NeoEloquent\Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vinelab\NeoEloquent\NeoEloquentServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            NeoEloquentServiceProvider::class
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        /** @var Repository $config */
        $config = $app->get('config');
        $config->set('database.default', 'neo4j');

        $connections = $config->get('database.connections');
        $connections = array_merge($connections, [
            'default' => [
                'driver' => 'neo4j',
                'host' => env('NEO4J_HOST', 'localhost'),
                'database' => env('NEO4J_DATABASE', 'neo4j'),
                'port' => env('NEO4J_PORT', 7687),
                'username' => env('NEO4J_USER', 'neo4j'),
                'password' => env('NEO4J_PASSWORD', 'testtest'),
            ],
            'neo4j' => [
                'driver' => 'neo4j',
                'host' => env('NEO4J_HOST', 'localhost'),
                'database' => env('NEO4J_DATABASE', 'neo4j'),
                'port' => env('NEO4J_PORT', 7687),
                'username' => env('NEO4J_USER', 'neo4j'),
                'password' => env('NEO4J_PASSWORD', 'testtest'),
            ]
        ]);
        $config->set('database.connections', $connections);
    }

    public function getAnnotations(): array
    {
        return [];
    }
}
