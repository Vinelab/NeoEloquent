<?php

/** @noinspection PhpUndefinedNamespaceInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace Vinelab\NeoEloquent;

use Arr;
use BadMethodCallException;
use Closure;
use Doctrine\DBAL\Types\Type;
use Generator;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Database\LostConnectionException;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\QueryException;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Exception\Neo4jException;
use Laudis\Neo4j\Types\CypherMap;
use Throwable;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Schema\Builder;
use Vinelab\NeoEloquent\Schema\CypherGrammar as SchemaGrammar;

final class Connection extends \Illuminate\Database\Connection
{
    /** @var list<UnmanagedTransactionInterface> */
    private array $activeTransactions = [];

    public function __construct(
        private readonly SessionInterface $readSession,
        private readonly SessionInterface $session,
        string $database,
        string $tablePrefix,
        array $config
    ) {
        parent::__construct(static fn () => null, $database, $tablePrefix, $config);
    }

    protected function getDefaultQueryGrammar(): CypherGrammar
    {
        return new CypherGrammar();
    }

    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        return new SchemaGrammar();
    }

    public function query(): Query\Builder
    {
        return new Query\Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    public function getSchemaBuilder(): Builder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }

    protected function getDefaultPostProcessor(): Processor
    {
        return new \Vinelab\NeoEloquent\Processor();
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
        if (count($this->activeTransactions)) {
            return Arr::last($this->activeTransactions);
        }

        return $this->getSession($read);
    }

    protected function run($query, $bindings, Closure $callback): mixed
    {
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        try {
            $result = $callback($query, $bindings);
        } catch (Throwable $e) {
            throw new QueryException(
                'bolt', $query, $this->prepareBindings($bindings), $e
            );
        }

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        return $result;
    }

    public function scalar($query, $bindings = [], $useReadPdo = true)
    {
        return Arr::first($this->selectOne($query, $bindings, $useReadPdo));
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending) {
                return;
            }

            /** @noinspection PhpParamsInspection */
            $this->event(new StatementPrepared($this, new Statement($query, $bindings)));

            yield from $this->getRunner($useReadPdo)
                ->run($query, array_merge($this->prepareBindings($bindings), $this->queryGrammar->getBoundParameters($query)))
                ->map(static fn (CypherMap $map) => $map->toArray());
        });
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        try {
            return iterator_to_array($this->cursor($query, $bindings, $useReadPdo));
        } catch (Neo4jException $e) {
            throw new QueryException($this->getName(), $query, $bindings, $e);
        }
    }

    public function statement($query, $bindings = []): bool
    {
        return (bool) $this->affectingStatement($query, $bindings);
    }

    public function selectResultSets($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            return [
                $this->select($query, $bindings, $useReadPdo),
            ];
        });
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending) {
                return true;
            }

            $parameters = array_merge($this->prepareBindings($bindings), $this->queryGrammar->getBoundParameters($query));
            $result = $this->getRunner()->run($query, $parameters);

            return $this->summarizeCounters($result->getSummary()->getCounters());
        });
    }

    public function unprepared($query): bool
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending) {
                return true;
            }

            $result = $this->getRunner()->run($query);
            $change = $this->summarizeCounters($result->getSummary()->getCounters()) > 0;

            $this->recordsHaveBeenModified($change);

            return true;
        });
    }

    public function insert($query, $bindings = []): bool
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Prepare the query bindings for execution.
     */
    public function prepareBindings(array $bindings): array
    {
        $tbr = [];

        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $tbr['param'.$key] = $value;
            } else {
                $tbr[$key] = $value;
            }
        }

        return $tbr;
    }

    public function beginTransaction(): void
    {
        $this->activeTransactions[] = $this->getSession()->beginTransaction();

        $this->transactionsManager?->begin(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit(): void
    {
        $this->fireConnectionEvent('committing');

        $this->popTransaction()?->commit();

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager?->commit($this->getName());
        }

        $this->fireConnectionEvent('committed');
    }

    private function popTransaction(): ?UnmanagedTransactionInterface
    {
        return count($this->activeTransactions) ? array_pop($this->activeTransactions) : null;
    }

    public function rollBack($toLevel = null): void
    {
        if (count($this->activeTransactions) === 0) {
            return;
        }

        $this->popTransaction()?->rollback();

        $this->fireConnectionEvent('rollingBack');
    }

    public function transactionLevel(): int
    {
        return count($this->activeTransactions);
    }

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

    public function selectOne($query, $bindings = [], $useReadPdo = true): array|null
    {
        foreach ($this->cursor($query, $bindings, $useReadPdo) as $result) {
            return $result;
        }

        return null;
    }

    public function transaction(Closure $callback, $attempts = 1): mixed
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $callbackResult = $callback($this);
            } catch (Neo4jException $e) {
                if ($e->getClassification() === 'Transaction') {
                    continue;
                } else {
                    throw $e;
                }
            }

            $this->commit();

            return $callbackResult;
        }

        return null;
    }

    public function bindValues($statement, $bindings)
    {

    }

    public function reconnect()
    {
        throw new LostConnectionException('Lost connection and no reconnector available.');
    }

    public function reconnectIfMissingConnection(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function isDoctrineAvailable(): bool
    {
        return false;
    }

    public function usingNativeSchemaOperations(): bool
    {
        return true;
    }

    public function getDoctrineColumn($table, $column)
    {
        throw new BadMethodCallException('Cannot use doctrine on graph databases');
    }

    public function getDoctrineSchemaManager()
    {
        throw new BadMethodCallException('Cannot use doctrine on graph databases');
    }

    public function getDoctrineConnection()
    {
        throw new BadMethodCallException('Cannot use doctrine on graph databases');
    }

    public function registerDoctrineType(Type|string $class, string $name, string $type): void
    {
        throw new BadMethodCallException('Cannot use doctrine on graph databases');
    }
}
