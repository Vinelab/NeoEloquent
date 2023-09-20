<?php

namespace Vinelab\NeoEloquent\Schema\Grammars;

use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Schema\Grammars\ChangeColumn;
use Illuminate\Database\Schema\Grammars\RenameColumn;
use function array_merge;
use function array_values;
use function collect;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use function implode;
use function is_null;
use RuntimeException;
use function sprintf;
use function trim;

class CypherGrammar extends Grammar
{
    public function compileCreateDatabase($name, $connection): string
    {
        return sprintf('CREATE DATABASE %s', $name);
    }

    public function compileDropDatabaseIfExists($name): string
    {
        return sprintf('DROP DATABASE %s IF EXISTS', $name);
    }

    /**
     * Compile the query to determine the list of tables.
     */
    public function compileTableExists(): string
    {
        return <<<'CYPHER'
        CALL db.labels()
        YIELD label
        WHERE label = $0
        RETURN label
        CYPHER;
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(string $table): string
    {
        return <<<CYPHER
        CALL db.schema.nodeTypeProperties()
        YIELD nodeLabels, propertyName
        WHERE [ label IN nodeLabels WHERE label = "$table" | label ] <> []
        RETURN propertyName as column_name
        CYPHER;
    }

    /**
     * @return list<string>
     */
    public function compileCreate(Blueprint $blueprint): array
    {
        return array_values(array_merge(
            $this->getColumns($blueprint),
            $this->compileAutoIncrementStartingValues($blueprint)
        ));
    }

    /**
     * @return list<string>
     */
    protected function getColumns(Blueprint $blueprint): array
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            // Each of the column types has their own compiler functions, which are tasked
            // with turning the column definition into its SQL format for this platform
            // used by the connection. The column's modifiers are compiled and added.
            $sql = $this->wrap($column).' '.$this->getType($column);

            $columns = array_merge($columns, $this->addModifiers($sql, $blueprint, $column));
        }

        return $columns;
    }

    /**
     * Add the column modifiers to the definition.
     *
     * @param  string  $sql
     * @param Blueprint $blueprint
     * @param Fluent $column
     *
     * @return list<string>
     */
    protected function addModifiers($sql, Blueprint $blueprint, Fluent $column): array
    {
        $tbr = [];

        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify{$modifier}")) {
                $modification = $this->{$method}($blueprint, $column);
                if (is_string($modification) && trim($modification) !== '') {
                    $tbr[] = $modification;
                }
            }
        }

        return $tbr;
    }

    /**
     * @return list<string>
     */
    public function compileAdd(Blueprint $blueprint): array
    {
        // adding or creating is syntactically the same in Neo4J.
        return $this->compileCreate($blueprint);
    }

    /**
     * Compile the auto-incrementing column starting values.
     *
     *
     * @return array
     */
    public function compileAutoIncrementStartingValues(Blueprint $blueprint)
    {
        return collect($blueprint->autoIncrementingStartingValues())->map(function ($value, $column) use ($blueprint) {
            return 'alter table '.$this->wrapTable($blueprint->getTable()).' auto_increment = '.$value;
        })->all();
    }

    /**
     * Compile a primary key command.
     *
     *
     * @return string
     */
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $command->name(null);

        return $this->compileKey($blueprint, $command, 'primary key');
    }

    /**
     * Compile a unique key command.
     *
     *
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'unique');
    }

    /**
     * Compile a plain index key command.
     *
     *
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'index');
    }

    /**
     * Compile a fulltext index key command.
     *
     *
     * @return string
     */
    public function compileFullText(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'fulltext');
    }

    /**
     * Compile a spatial index key command.
     *
     *
     * @return string
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'spatial index');
    }

    /**
     * Compile an index creation command.
     *
     * @param  string  $type
     * @return string
     */
    protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
    {
        return sprintf('alter table %s add %s %s%s(%s)',
            $this->wrapTable($blueprint),
            $type,
            $this->wrap($command->index),
            $command->algorithm ? ' using '.$command->algorithm : '',
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a drop table command.
     *
     *
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     *
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists '.$this->wrapTable($blueprint);
    }

    /**
     * Compile a drop column command.
     *
     *
     * @return string
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));

        return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }

    /**
     * Compile a drop primary key command.
     *
     *
     * @return string
     */
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table '.$this->wrapTable($blueprint).' drop primary key';
    }

    /**
     * Compile a drop unique key command.
     *
     *
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }

    /**
     * Compile a drop index command.
     *
     *
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }

    /**
     * Compile a drop fulltext index command.
     *
     *
     * @return string
     */
    public function compileDropFullText(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDropIndex($blueprint, $command);
    }

    /**
     * Compile a drop spatial index command.
     *
     *
     * @return string
     */
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDropIndex($blueprint, $command);
    }

    /**
     * Compile a drop foreign key command.
     *
     *
     * @return string
     */
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop foreign key {$index}";
    }

    /**
     * Compile a rename table command.
     *
     *
     * @return string
     */
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);

        return "rename table {$from} to ".$this->wrapTable($command->to);
    }

    /**
     * Compile a rename index command.
     *
     *
     * @return string
     */
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s rename index %s to %s',
            $this->wrapTable($blueprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }

    /**
     * Compile the SQL needed to drop all tables.
     *
     * @param  array  $tables
     * @return string
     */
    public function compileDropAllTables($tables)
    {
        return 'drop table '.implode(',', $this->wrapArray($tables));
    }

    /**
     * Compile the SQL needed to retrieve all table names.
     *
     * @return string
     */
    public function compileGetAllTables()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';
    }

    /**
     * Compile the command to enable foreign key constraints.
     *
     * @return string
     */
    public function compileEnableForeignKeyConstraints()
    {
        return 'RETURN true as true';
    }

    /**
     * Compile the command to disable foreign key constraints.
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'RETURN true as true';
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        throw new RuntimeException('Wrapping of values is not allowed');
    }

    /**
     * Compile a foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        // Neo4j does not support foreign keys, but we will totally accept this mistake
        // to improve maintainability.
        // TODO - provided indexes instead
        return 'RETURN true AS true';
    }

    /**
     * Compile a rename column command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @param  \Illuminate\Database\Connection  $connection
     * @return array|string
     */
    public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return RenameColumn::compile($this, $blueprint, $command, $connection);
    }

    /**
     * Compile a change column command into a series of SQL statements.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @param  \Illuminate\Database\Connection  $connection
     * @return array|string
     *
     * @throws \RuntimeException
     */
    public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return ChangeColumn::compile($this, $blueprint, $command, $connection);
    }


    /**
     * Create an empty Doctrine DBAL TableDiff from the Blueprint.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Doctrine\DBAL\Schema\AbstractSchemaManager  $schema
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    public function getDoctrineTableDiff(Blueprint $blueprint, SchemaManager $schema)
    {
        $tableName = $this->getTablePrefix().$blueprint->getTable();

        $table = $schema->introspectTable($tableName);

        return new TableDiff(tableName: $tableName, fromTable: $table);
    }

    /**
     * Format a value so that it can be used in "default" clauses.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function getDefaultValue($value)
    {
        if ($value instanceof Expression) {
            return $this->getValue($value);
        }

        return is_bool($value)
            ? "'".(int) $value."'"
            : "'".(string) $value."'";
    }
}
