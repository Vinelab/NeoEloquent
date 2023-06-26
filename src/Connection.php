<?php

namespace Vinelab\NeoEloquent;

use BadMethodCallException;
use Closure;
use Doctrine\DBAL\Types\Type;
use Generator;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Database\LostConnectionException;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Laudis\Neo4j\Databags\ResultSummary;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Exception\Neo4jException;
use Laudis\Neo4j\Types\CypherMap;
use LogicException;
use RuntimeException;
use Throwable;
use Vinelab\NeoEloquent\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Schema\Builder;
use Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar as SchemaGrammar;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Connection extends \Illuminate\Database\Connection
{
    /** @var UnmanagedTransactionInterface[] */
    private array $activeTransactions = [];

    private SummaryCounters $totals;

    public function escape($value, $binary = false)
    {
        throw new RuntimeException('Escaping from the connection is not supported yet.');
    }

    public function hasModifiedRecords(): bool
    {
        return $this->totals->containsUpdates();
    }

    public function recordsHaveBeenModified($value = true)
    {
        throw new RuntimeException('Record modification is handled by summary totals in this connection.');
    }

    public function setRecordModificationState(bool $value)
    {
        throw new RuntimeException('Record modification is handled by summary totals in this connection.');
    }

    public function forgetRecordModificationState(): void
    {
        $this->totals = new SummaryCounters();
    }

    public function getPdo(): SessionInterface
    {
        return $this->session;
    }

    public function getRawPdo(): SessionInterface
    {
        return $this->session;
    }

    public function getReadPdo(): SessionInterface
    {
        return $this->readSession;
    }

    public function getRawReadPdo(): SessionInterface
    {
        return $this->readSession;
    }


    public function __construct(
        private readonly SessionInterface $readSession,
        private readonly SessionInterface $session,
        string $database,
        string $tablePrefix,
        array $config
    ) {
        parent::__construct(static fn () => throw new LogicException('Cannot use PDO in '. self::class), $database, $tablePrefix, $config);

        $this->useDefaultSchemaGrammar();

        $this->totals = new SummaryCounters();
    }

    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        return new SchemaGrammar();
    }

    protected function getDefaultQueryGrammar(): CypherGrammar
    {
        return new CypherGrammar();
    }

    public function query(): Query\Builder
    {
        return new Query\Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    public function getSchemaBuilder(): Builder
    {
        return new Builder($this);
    }

    protected function getDefaultPostProcessor(): Processor
    {
        return new Processors\Processor();
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
        $autobound = CypherGrammar::getBindings($query);
        if (count($autobound) === 0) {
            CypherGrammar::setBindings($query, $bindings);
        } else {
            $bindings = $autobound;
        }
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        $this->reconnectIfMissingConnection();

        $start = (int) microtime(true);

        try {
            $result = $callback($query);
        } catch (Throwable $e) {
            throw new QueryException('bolt', $query, $bindings, $e);
        }

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        return $result;
    }

    public function scalar($query, $bindings = [], $useReadPdo = true)
    {
        return Arr::first($this->selectOne($query, $bindings, $useReadPdo) ?? []);
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->run($query, $bindings, function (string $query) use ($useReadPdo) {
            if ($this->pretending) {
                return;
            }

            $statement = new Statement($query, CypherGrammar::getBindings($query));
            /**
             * @noinspection PhpParamsInspection
             * @psalm-suppress InvalidArgument
             */
            $this->event(new StatementPrepared($this, $statement));

            yield from $this->getRunner($useReadPdo)
                ->runStatement($statement)
                ->map(static fn (CypherMap $map) => $map->toArray());
        });
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        try {
            return iterator_to_array($this->cursor($query, $bindings, $useReadPdo));
        } catch (Neo4jException $e) {
            throw new QueryException($this->getName() ?? '', $query, $bindings, $e);
        }
    }

    public function statement($query, $bindings = []): bool
    {
        return (bool) $this->affectingStatement($query, $bindings);
    }

    public function selectResultSets($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->run($query, $bindings, function (string $query) use ($useReadPdo) {
            return [
                $this->select($query, useReadPdo: $useReadPdo),
            ];
        });
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->run($query, $bindings, function (string $query) {
            if ($this->pretending) {
                return true;
            }

            $result = $this->getRunner()->run($query, CypherGrammar::getBindings($query));

            return $this->summarizeCounters($result->getSummary()->getCounters());
        });
    }

    public function unprepared($query): bool
    {
        return $this->run($query, [], function (string $query) {
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
        return (bool) $this->affectingStatement($query);
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

        $this->transactionsManager->begin($this->getName() ?? '', $this->transactions);

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit(): void
    {
        $this->fireConnectionEvent('committing');

        $this->popTransaction()?->commit();

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager->commit($this->getName() ?? '');
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
        foreach ($this->cursor($query, useReadPdo:  $useReadPdo) as $result) {
            return $result;
        }

        return null;
    }

    public function transaction(Closure $callback, $attempts = 1): mixed
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                /** @psalm-suppress ArgumentTypeCoercion */
                $callbackResult = $callback($this);
            } catch (Neo4jException $e) {
                if ($e->getClassification() === 'Transaction') {
                    continue;
                } else {
                    throw $e;
                }
            }

            $this->commit();

            /** @psalm-suppress PossiblyUndefinedVariable */
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
