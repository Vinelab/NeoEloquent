<?php

namespace Vinelab\NeoEloquent;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Common\Uri;
use Laudis\Neo4j\Databags\DriverConfiguration;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Laudis\Neo4j\Enum\AccessMode;
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
        $port = (is_null($port) || $port === '') ? null : ((int) $port);
        $uri = $this->defaultUri->withScheme($config['scheme'] ?? '')
            ->withHost($config['host'] ?? '')
            ->withPort($port);

        if (($config['username'] ?? false) && ($config['password'] ?? false)) {
            $auth = Authenticate::basic($config['username'], $config['password']);
        } else {
            $auth = Authenticate::disabled();
        }

        $driver = Driver::create($uri, DriverConfiguration::default(), $auth);
        $config = SessionConfiguration::default()
            ->withDatabase($database);
        return new Connection(
            $driver->createSession($config->withAccessMode(AccessMode::READ())),
            $driver->createSession(),
            $database,
            $prefix
        );
    }
}