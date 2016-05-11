<?php

namespace Vinelab\NeoEloquent\Tests;

use Neoxygen\NeoClient\Client;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Container\Container;
use Vinelab\NeoEloquent\Connectors\ConnectionFactory;

class ConnectionFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->factory = new ConnectionFactory(new Container());
    }

    public function tearDown()
    {
    }

    public function testSingleConnection()
    {
        $config = [
            'connections' => [

                'default' => [
                    'host' => 'server.host',
                    'port' => 7474,
                    'username' => 'theuser',
                    'password' => 'thepass',
                ],

            ],
        ];

        $connection = $this->factory->make($config);
        $client = $connection->getClient();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(Client::class, $client);

        $this->assertEquals($config['connections'], $connection->getConfig()['connections']);

        $clientConnection = $client->getConnection();
        $params = $config['connections']['default'];

        $this->assertEquals('default', $clientConnection->getAlias());
        $this->assertEquals($params['host'], $clientConnection->getHost());
        $this->assertEquals($params['port'], $clientConnection->getPort());
        $this->assertEquals($params['username'], $clientConnection->getAuthUser());
        $this->assertEquals($params['password'], $clientConnection->getAuthPassword());
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
        $this->assertInstanceOf(Client::class, $connection->getClient());

        $client = $connection->getClient();

        $defaultConnection = $client->getConnection();
        $this->assertEquals($config['connections']['server1']['host'], $defaultConnection->getHost());
        $this->assertEquals(7474, $defaultConnection->getPort());
        $this->assertEquals($config['connections']['server1']['username'], $defaultConnection->getAuthUser());
        $this->assertEquals($config['connections']['server1']['password'], $defaultConnection->getAuthPassword());

        $this->assertEquals($config['default'], $connection->getConfig()['default']);
        $this->assertEquals($config['connections'], $connection->getConfig()['connections']);
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

        $connection = $this->factory->make($config);
    }
}
