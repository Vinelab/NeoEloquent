<?php

namespace Vinelab\NeoEloquent;

use Closure;
use Generator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Enum\AccessMode;
use Laudis\Neo4j\Exception\Neo4jException;
use Laudis\Neo4j\Types\CypherMap;
use LogicException;
use Vinelab\NeoEloquent\Query\Builder;

final class Connection implements ConnectionInterface
{
    private Driver $driver;
    private Grammar $grammar;
    private string $database;
    private ?UnmanagedTransactionInterface $tsx = null;
    private bool $pretending = false;

    public function __construct(Driver $driver, Grammar $grammar, string $database)
    {
        $this->driver = $driver;
        $this->grammar = $grammar;
        $this->database = $database;
    }

    public function table($table, $as = null)
    {
        return (new Builder($this, $this->grammar))->from($table, $as);
    }

    private function getSession(bool $useReadPdo = false, int $limit = null): TransactionInterface
    {
        $config = SessionConfiguration::default();
        if ($useReadPdo) {
            $config = $config->withAccessMode(AccessMode::READ());
        }

        if ($limit !== null) {
            $config = $config->withFetchSize($limit);
        }

        return $this->driver->createSession($config);
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        if ($this->pretending) {
            return;
        }

        yield from $this->getSession($useReadPdo)
            ->run($query, $bindings)
            ->map(static fn (CypherMap $map) => $map->toArray());
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }

    public function raw($value): Expression
    {
        return new Expression($value);
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true): array
    {
        if ($this->pretending) {
            return [];
        }

        return $this->getSession($useReadPdo, 1)
            ->run($query, $bindings)
            ->getAsCypherMap(0)
            ->toArray();
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        if ($this->pretending) {
            return [];
        }

        return $this->getSession($useReadPdo)
            ->run($query, $bindings)
            ->map(static fn (CypherMap $map) => $map->toArray())
            ->toArray();
    }

    public function insert($query, $bindings = []): bool
    {
        if ($this->pretending) {
            return true;
        }

        $this->getSession()->run($query, $bindings);

        return true;
    }

    public function update($query, $bindings = []): int
    {
        if ($this->pretending) {
            return 0;
        }

        $counters = $this->getSession()->run($query, $bindings)->getSummary()->getCounters();

        return $this->summarizeCounters($counters);
    }

    public function delete($query, $bindings = []): int
    {
        if ($this->pretending) {
            return 0;
        }

        $counters = $this->getSession()->run($query, $bindings)->getSummary()->getCounters();

        return $this->summarizeCounters($counters);
    }

    public function statement($query, $bindings = []): bool
    {
        if ($this->pretending) {
            return true;
        }

        $this->getSession()->run($query, $bindings);

        return true;
    }

    public function affectingStatement($query, $bindings = []): int
    {
        if ($this->pretending) {
            return 0;
        }

        $counters = $this->getSession()->run($query, $bindings)->getSummary()->getCounters();

        return $this->summarizeCounters($counters);
    }

    public function unprepared($query): bool
    {
        if ($this->pretending) {
            return 0;
        }

        $this->getSession()->run($query);

        return true;
    }

    public function prepareBindings(array $bindings): array
    {
        return $bindings;
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 0; $currentAttempt < $attempts; $currentAttempt++) {
            try {
                $this->beginTransaction();
                $callbackResult = $callback($this);
                $this->commit();
            } catch (Neo4jException $e) {
                if ($e->getCategory() === 'Transaction') {
                    continue;
                }

                throw $e;
            }

            return $callbackResult;
        }

        throw new LogicException('Transaction attempt limit reached');
    }

    public function beginTransaction(): void
    {
        $session = $this->getSession();
        if (!$session instanceof SessionInterface) {
            throw new LogicException('There is already a transaction bound to the connection');
        }

        $this->tsx = $session->beginTransaction();
    }

    public function commit(): void
    {
        if ($this->tsx !== null) {
            $this->tsx->commit();
            $this->tsx = null;
        }
    }

    public function rollBack(): void
    {
        if ($this->tsx !== null) {
            $this->tsx->rollback();
            $this->tsx = null;
        }
    }

    public function transactionLevel(): int
    {
        return $this->tsx === null ? 0 : 1;
    }

    public function pretend(Closure $callback)
    {
        $this->pretending = true;
        $callback($this);
        $this->pretending = false;
    }

    /**
     * @param SummaryCounters $counters
     * @return int
     */
    private function summarizeCounters(SummaryCounters $counters): int
    {
        return $counters->propertiesSet() +
            $counters->labelsAdded() +
            $counters->labelsRemoved() +
            $counters->nodesCreated() +
            $counters->nodesDeleted() +
            $counters->relationshipsCreated() +
            $counters->relationshipsDeleted();
    }
}
