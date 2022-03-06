<?php

namespace Vinelab\NeoEloquent;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Common\Uri;
use Laudis\Neo4j\Databags\DriverConfiguration;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Vinelab\NeoEloquent\Schema\Grammars\Grammar;
use function array_key_exists;

final class ConnectionFactory
{
    private Uri $defaultUri;
    private Grammar $grammar;
    private SessionConfiguration $config;

    public function __construct(Uri $defaultUri, Grammar $grammar, SessionConfiguration $config)
    {
        $this->defaultUri = $defaultUri;
        $this->grammar = $grammar;
        $this->config = $config;
    }

    public static function default(): self
    {
        return new self(Uri::create(), new Grammar(), SessionConfiguration::default());
    }

    /**
     * @param array{scheme?: string, driver: string, host?: string, port?: string|int, username ?: string, password ?: string, database ?: string} $config
     */
    public function make(array $config): Connection
    {
        $uri = $this->defaultUri->withScheme($config['scheme'] ?? '')
            ->withHost($config['host'] ?? '')
            ->withPort($config['port'] ?? null);

        if (array_key_exists('username', $config) && array_key_exists('password', $config)) {
            $auth = Authenticate::basic($config['username'], $config['password']);
        } else {
            $auth = Authenticate::disabled();
        }

        $sessionConfig = $this->config;
        if (array_key_exists('database', $config)) {
            $sessionConfig = $sessionConfig->withDatabase($config['database']);
        }

        return new Connection(
            Driver::create($uri, DriverConfiguration::default(), $auth),
            $this->grammar,
            $sessionConfig
        );
    }
}