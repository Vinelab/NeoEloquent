<?php

namespace Vinelab\NeoEloquent\Connectors;

use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Exceptions\Exception;

class Neo4jConnector
{
    public function connect($type, $config)
    {
        $connection = new Connection($config);

        switch ($type) {
            case Connection::TYPE_SINGLE:
                $client = $connection->createSingleConnectionClient($config);
                break;

            case Connection::TYPE_MULTI:
                $client = $connection->createMultipleConnectionsClient($config);
                break;

            case Connection::TYPE_HA:
                $client = $connection->createHAClient($config);
                break;
            default:
                throw new Exception('Unsupported connection type ' + $type);
                break;
        }

        $connection->setClient($client);

        return $connection;
    }
}
