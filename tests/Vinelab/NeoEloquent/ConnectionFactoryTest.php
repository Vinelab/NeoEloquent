<?php

namespace Vinelab\NeoEloquent\Tests;

use Exception;
use Laudis\Neo4j\Contracts\ClientInterface;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Connectors\ConnectionFactory;
use Illuminate\Container\Container;

class ConnectionFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->factory = new ConnectionFactory(new Container());
    }

    public function tearDown(): void
    {
    }

    public function testSingleConnection()
    {
        $config = [
            'type' => 'single',
            'host' => 'server.host',
            'port' => 7474,
            'username' => 'theuser',
            'password' => 'thepass',
        ];

        $connection = $this->factory->make($config);
        $client = $connection->getClient();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(ClientInterface::class, $client);

        $this->assertEquals($config, $connection->getConfig());
    }

    public function testMultipleConnections()
    {
        $config = [

            'default' => 'server1',

            'connections' => [

                'server1' => [
                    'host' => 'server1.host',
                    'username' => 'theuser',
                    'password' => 'thepass',
                ],

                'server2' => [
                    'host' => 'server2.host',
                    'username' => 'anotheruser',
                    'password' => 'anotherpass',
                ],

            ],

        ];

        $connection = $this->factory->make($config);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(ClientInterface::class, $connection->getClient());
    }

    public function testHAConnection()
    {
        $config = [
            'replication' => true,

            'connections' => [

               'master' => [
                    'host' => 'server1.ip.address',
                    'username' => 'theuser',
                    'password' => 'dapass',
               ],

               'slaves' => [
                    'slave-1' => [
                        'host' => 'server2.ip.address',
                        'username' => 'anotheruser',
                        'password' => 'somepass',
                    ],
                   'slave-2' => [
                        'host' => 'server3.ip.address',
                        'username' => 'moreusers',
                        'password' => 'differentpass',
                    ],
               ],

            ],
        ];

        $this->expectException(Exception::class);
        $this->expectErrorMessage('High Availability mode is not supported anymore. Please use the neo4j scheme instead');
        $this->factory->make($config);
    }
}
