<?php

namespace Vinelab\NeoEloquent;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Common\Uri;
use Laudis\Neo4j\Databags\DriverConfiguration;
use function array_key_exists;

final class ConnectionFactory
{
    private Uri $defaultUri;

    public function __construct(Uri $defaultUri = null)
    {
        $this->defaultUri = $defaultUri ?? Uri::create();
    }

    /**
     * @param array{scheme?: string, driver: string, host?: string, port?: string|int, username ?: string, password ?: string, database ?: string} $config
     */
    public function make(string $database, string $prefix, array $config): Connection
    {
        $port = $config['port'] ?? null;
        $port = is_null($port) ? $port : ((int) $port);
        $uri = $this->defaultUri->withScheme($config['scheme'] ?? '')
            ->withHost($config['host'] ?? '')
            ->withPort($port);

        if (array_key_exists('username', $config) && array_key_exists('password', $config)) {
            $auth = Authenticate::basic($config['username'], $config['password']);
        } else {
            $auth = Authenticate::disabled();
        }

        return new Connection(
            new Neo4JReconnector(Driver::create($uri, DriverConfiguration::default(), $auth), $database),
            $database,
            $prefix,
            $config
        );
    }
}