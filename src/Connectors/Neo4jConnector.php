<?php

namespace Vinelab\NeoEloquent\Connectors;

use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Exceptions\Exception;

class Neo4jConnector
{
    public function connect($type, $config)
    {
        $connection = new Connection($config);

        switch($type)
        {
            case Connection::TYPE_SINGLE:
                $client = $connection->createSingleConnectionClient($config);
                break;

            case Connection::TYPE_MULTI:
                $client = $connection->createMultipleConnectionsClient($config);
                break;

            case Connection::TYPE_HA:
                throw new \Exception('High Availability mode is not supported anymore. Please use the neo4j scheme instead');
                break;
            default:
                throw new Exception('Unsupported connection type '+$type);
                break;
        }

        $connection->setClient($client);

        return $connection;
    }
}
