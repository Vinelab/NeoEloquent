<?php

namespace Vinelab\NeoEloquent;

use Closure;
use Generator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Exception\Neo4jException;
use Laudis\Neo4j\Types\CypherMap;
use Throwable;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Query\CypherGrammar;

final class Connection implements ConnectionInterface
{
    /** @var array<UnmanagedTransactionInterface> */
    private array $transactions = [];
    private bool $pretending = false;

    public function __construct(
        private SessionInterface $readSession,
        private SessionInterface $session,
        private string           $database,
        private string           $tablePrefix,
        private array            $config
    )
    {
    }

    public function getConfig(string $option = null): ?string
    {
        return Arr::get($this->config, $option);
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getConfig('name');
    }

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->getConfig('driver');
    }

    public function getSession(bool $read = false): SessionInterface
    {
        if ($read) {
            return $this->readSession;
        }

        return $this->session;
    }

    public function getRunner(bool $read = false): TransactionInterface
    {
        if (count($this->transactions)) {
            return \Arr::last($this->transactions);
        }

        return $this->getSession($read);
    }

    public function table($table, $as = null): Builder
    {
        $grammar = new CypherGrammar();
        $grammar->setTablePrefix($this->tablePrefix);

        $builder = new Builder($this, $grammar, new Processor());

        return $builder->from($table, $as);
    }

    private function run(string $query, array $bindings, Closure $callback): mixed
    {
        try {
            $result = $callback($query, $bindings);
        } catch (Throwable $e) {
            throw new QueryException(
                'CYPHER', $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending) {
                return;
            }

            yield from $this->getRunner($useReadPdo)
                ->run($query, $this->prepareBindings($bindings))
                ->map(static fn(CypherMap $map) => $map->toArray());
        });
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        return iterator_to_array($this->cursor($query, $bindings, $useReadPdo));
    }

    /**
     * Execute an SQL statement and return the result.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return mixed
     */
    public function statement($query, $bindings = []): bool
    {
        return (bool)$this->affectingStatement($query, $bindings);
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending) {
                return true;
            }

            $parameters = $this->prepareBindings($bindings);
            $result = $this->getRunner()->run($query, $parameters);

            return $this->summarizeCounters($result->getSummary()->getCounters());
        });
    }

    public function unprepared($query): bool
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending) {
                return false;
            }

            $this->getRunner()->run($query);

            return true;
        });
    }

    public function insert($query, $bindings = []): bool
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings): array
    {
        $tbr = [];

        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $tbr['param' . $key] = $value;
            } else {
                $tbr[$key] = $value;
            }
        }

        return $tbr;
    }

    public function beginTransaction(): void
    {
        $this->transactions[] = $this->getSession()->beginTransaction();
    }

    public function commit(): void
    {
        $this->popTransaction()?->commit();
    }

    private function popTransaction(): ?UnmanagedTransactionInterface
    {
        return count($this->transactions) ? array_pop($this->transactions) : null;
    }

    public function rollBack($toLevel = null): void
    {
        $this->popTransaction()?->rollback();
    }

    public function transactionLevel(): int
    {
        return count($this->transactions);
    }

    /**
     * @param SummaryCounters $counters
     *
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

    public function raw($value): Expression
    {
        return new Expression($value);
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return $this->cursor($query, $bindings, $useReadPdo)->current();
    }

    public function update($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function delete($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $callbackResult = $callback($this);
            }

            catch (Neo4jException $e) {
                if ($e->getClassification() === 'Transaction') {
                    continue;
                } else {
                    throw $e;
                }
            }

            $this->commit();

            return $callbackResult;
        }
    }

    public function pretend(Closure $callback): array
    {
        $this->pretending = true;

        $callback($this);

        $this->pretending = false;

        return [];
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }
}
