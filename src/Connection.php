<?php

namespace Vinelab\NeoEloquent;

use BadMethodCallException;
use Bolt\error\ConnectException;
use Closure;
use DateTimeInterface;
use Generator;
use Illuminate\Database\QueryException;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Types\CypherMap;
use LogicException;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Schema\Grammars\Grammar;
use function array_filter;
use function get_debug_type;
use function is_bool;

final class Connection extends \Illuminate\Database\Connection
{
    private ?UnmanagedTransactionInterface $tsx = null;
    private array $committedCallbacks = [];

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        if ($pdo instanceof Neo4JReconnector) {
            $readPdo = Closure::fromCallable($pdo->withReadConnection());
            $pdo = Closure::fromCallable($pdo);
        } else {
            $readPdo = $pdo;
        }
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $this->setPdo($pdo);
        $this->setReadPdo($readPdo);
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param Closure|Builder|string $label
     * @param  string|null  $as
     */
    public function node($label, ?string $as = null): Query\Builder
    {
        return $this->table($label, $as);
    }

    public function query(): Builder
    {
        return new Builder($this, $this->getQueryGrammar(), $this->getPostProcessor());
    }

    public function table($table, $as = null): Builder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::table($table, $as);
    }

    public function getSession(bool $readSession = false): TransactionInterface
    {
        if ($this->tsx) {
            return $this->tsx;
        }

        if ($readSession) {
            $session = $this->getReadPdo();
        } else {
            $session = $this->getPdo();
        }

        if (!$session instanceof SessionInterface) {
            $msg = 'Reconnectors or PDO\'s must return "%s", Got "%s"';
            throw new LogicException(sprintf($msg, SessionInterface::class, get_debug_type($session)));
        }

        return $session;
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return;
            }

            yield from $this->getSession($useReadPdo)
                ->run($query, $this->prepareBindings($bindings))
                ->map(static fn (CypherMap $map) => $map->toArray());
        });

    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            return $this->getSession($useReadPdo)
                ->run($query, $this->prepareBindings($bindings))
                ->map(static fn (CypherMap $map) => $map->toArray())
                ->toArray();
        });
    }

    protected function getDefaultPostProcessor(): Processor
    {
        return new Processor();
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement($query, $bindings = []): bool
    {
        $this->affectingStatement($query, $bindings);

        return true;
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $result = $this->getSession()->run($query, $this->prepareBindings($bindings));
            if ($result->getSummary()->getCounters()->containsUpdates()) {
                $this->recordsHaveBeenModified();
            }

            return $this->summarizeCounters($result->getSummary()->getCounters());
        });
    }

    public function unprepared($query): bool
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return 0;
            }

            $this->getSession()->run($query);

            return true;
        });
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings): array
    {
        $grammar = $this->getQueryGrammar();
        $tbr = [];

        $bindings = array_values(array_filter($bindings, static fn ($x) => ! $x instanceof LabelAction));

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }

            $tbr['param'.$key] = $bindings[$key];
        }

        return $tbr;
    }

    public function beginTransaction(): void
    {
        $session = $this->getSession();
        if ($session instanceof SessionInterface) {
            $this->tsx = $session->beginTransaction();
        }

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit(): void
    {
        if ($this->tsx !== null) {
            $this->tsx->commit();
            $this->tsx = null;

            foreach ($this->committedCallbacks as $callback) {
                $callback($this);
            }
        }

        $this->fireConnectionEvent('committed');
    }

    public function rollBack($toLevel = null): void
    {
        if ($this->tsx !== null) {
            $this->tsx->rollback();
            $this->tsx = null;
        }

        $this->fireConnectionEvent('rollingBack');
    }

    public function transactionLevel(): int
    {
        return $this->tsx === null ? 0 : 1;
    }


    /**
     * Execute the callback after a transaction commits.
     *
     * @param  callable $callback
     */
    public function afterCommit($callback): void
    {
        $this->committedCallbacks[] = $callback;
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

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar(): CypherGrammar
    {
        return new CypherGrammar();
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): Grammar
    {
        return new Grammar();
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  mixed $statement
     * @param  mixed  $bindings
     */
    public function bindValues($statement, $bindings): void
    {
        return;
    }

    protected function handleQueryException(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($e->getPrevious() instanceof ConnectException) {
            throw $e;
        }

        return $this->runQueryCallback($query, $bindings, $callback);
    }

    /**
     * Is Doctrine available?
     *
     * @return bool
     */
    public function isDoctrineAvailable(): bool
    {
        // Doctrine is not available for neo4j
        return false;
    }

    /**
     * Get a Doctrine Schema Column instance.
     *
     * @param  string  $table
     * @param  string  $column
     */
    public function getDoctrineColumn($table, $column): void
    {
        throw new BadMethodCallException('Doctrine is not available for Neo4J connections');
    }

    /**
     * Get the Doctrine DBAL schema manager for the connection.
     */
    public function getDoctrineSchemaManager(): void
    {
        throw new BadMethodCallException('Doctrine is not available for Neo4J connections');
    }

    /**
     * Get the Doctrine DBAL database connection instance.
     */
    public function getDoctrineConnection(): void
    {
        throw new BadMethodCallException('Doctrine is not available for Neo4J connections');
    }

    /**
     * Register a custom Doctrine mapping type.
     */
    public function registerDoctrineType(string $class, string $name, string $type): void
    {
        throw new BadMethodCallException('Doctrine is not available for Neo4J connections');
    }
}
