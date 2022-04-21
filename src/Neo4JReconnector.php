<?php

namespace Vinelab\NeoEloquent;

use Closure;
use Laudis\Neo4j\Contracts\DriverInterface;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Laudis\Neo4j\Enum\AccessMode;

class Neo4JReconnector
{
    private DriverInterface $driver;
    private bool $readConnection;
    private string $database;

    public function __construct(DriverInterface $driver, string $database, bool $readConnection = false)
    {
        $this->driver = $driver;
        $this->readConnection = $readConnection;
        $this->database = $database;
    }

    public function withReadConnection(bool $readConnection = true): self
    {
        return new self($this->driver, $readConnection);
    }

    public function __invoke(): SessionInterface
    {
        $config = SessionConfiguration::default()->withDatabase($this->database);
        if ($this->readConnection) {
            $config = $config->withAccessMode(AccessMode::READ());
        }

        return $this->driver->createSession($config);
    }
}