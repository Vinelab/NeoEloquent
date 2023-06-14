<?php

namespace Vinelab\NeoEloquent\Connectors;

use Illuminate\Database\Connectors\ConnectorInterface;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Common\Uri;
use Laudis\Neo4j\Databags\DriverConfiguration;

final class ConnectionFactory implements ConnectorInterface
{
    private Uri $defaultUri;

    public function __construct(Uri $defaultUri = null)
    {
        $this->defaultUri = $defaultUri ?? Uri::create();
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @param  array{scheme?: string, driver: string, host?: string, port?: string|int, username ?: string, password ?: string, database ?: string, prefix ?: string}  $config
     */
    public function connect(array $config): Driver
    {
        $port = $config['port'] ?? null;
        $port = (is_null($port) || $port === '') ? null : ((int) $port);
        $uri = $this->defaultUri->withScheme($config['scheme'] ?? '')
            ->withHost($config['host'] ?? '')
            ->withPort($port);

        if (array_key_exists('username', $config) && array_key_exists('password', $config)) {
            $auth = Authenticate::basic($config['username'], $config['password']);
        } else {
            $auth = Authenticate::disabled();
        }

        return Driver::create($uri, DriverConfiguration::default(), $auth);
    }
}
