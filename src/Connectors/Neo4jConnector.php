<?php

namespace Vinelab\NeoEloquent\Connectors;

use RuntimeException;
use Vinelab\NeoEloquent\Connection;

class Neo4jConnector
{
    public function connect($type, $config): Connection
    {
        $connection = new Connection($config);

        switch($type)
        {
            case Connection::TYPE_SINGLE:
                $client = $connection->createSingleConnectionClient();
                break;

            case Connection::TYPE_MULTI:
                $client = $connection->createMultipleConnectionsClient();
                break;
            default:
                throw new RuntimeException('Unsupported connection type '.$type);
        }

        $connection->setClient($client);

        return $connection;
    }
}
