<?php

namespace Vinelab\NeoEloquent\Schema\Grammars;

use function array_merge;
use function array_values;
use BadMethodCallException;
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
WHERE label = $param0
RETURN label
CYPHER;
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(): string
    {
        return <<<'CYPHER'
CALL db.schema.nodeTypeProperties()
YIELD nodeLabels, propertyName
WHERE [ label IN nodeLabels WHERE label = $param0 | label ] <> []
RETURN propertyName as column_name
CYPHER;
    }

    /**
     * Compile a create table command.
     *
     *
     * @return array
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return [];
        //        $sql = $this->compileCreateTable(
        //            $blueprint, $command, $connection
        //        );
        //
        //        // Once we have the primary SQL, we can add the encoding option to the SQL for
        //        // the table.  Then, we can check if a storage engine has been supplied for
        //        // the table. If so, we will add the engine declaration to the SQL query.
        //        $sql = $this->compileCreateEncoding(
        //            $sql, $connection, $blueprint
        //        );
        //
        //        // Finally, we will append the engine configuration onto this SQL statement as
        //        // the final thing we do before returning this finished SQL. Once this gets
        //        // added the query will be ready to execute against the real connections.
        //        return array_values(array_filter(array_merge([$this->compileCreateEngine(
        //            $sql, $connection, $blueprint
        //        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }

    /**
     * Create the main create table clause.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @param  Connection  $connection
     * @return array
     */
    protected function compileCreateTable($blueprint, $command, $connection)
    {
        return trim(sprintf('%s table %s (%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        ));
    }

    /**
     * Append the character set specifications to a command.
     *
     * @param  string  $sql
     * @return string
     */
    protected function compileCreateEncoding($sql, Connection $connection, Blueprint $blueprint)
    {
        // First we will set the character set if one has been set on either the create
        // blueprint itself or on the root configuration for the connection that the
        // table is being created on. We will add these to the create table query.
        if (isset($blueprint->charset)) {
            $sql .= ' default character set '.$blueprint->charset;
        } elseif (! is_null($charset = $connection->getConfig('charset'))) {
            $sql .= ' default character set '.$charset;
        }

        // Next we will add the collation to the create table statement if one has been
        // added to either this create table blueprint or the configuration for this
        // connection that the query is targeting. We'll add it to this SQL query.
        if (isset($blueprint->collation)) {
            $sql .= " collate '{$blueprint->collation}'";
        } elseif (! is_null($collation = $connection->getConfig('collation'))) {
            $sql .= " collate '{$collation}'";
        }

        return $sql;
    }

    /**
     * Append the engine specifications to a command.
     *
     * @param  string  $sql
     * @return string
     */
    protected function compileCreateEngine($sql, Connection $connection, Blueprint $blueprint)
    {
        if (isset($blueprint->engine)) {
            return $sql.' engine = '.$blueprint->engine;
        } elseif (! is_null($engine = $connection->getConfig('engine'))) {
            return $sql.' engine = '.$engine;
        }

        return $sql;
    }

    /**
     * Compile an add column command.
     *
     *
     * @return array
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('add', $this->getColumns($blueprint));

        return array_values(array_merge(
            ['alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns)],
            $this->compileAutoIncrementStartingValues($blueprint)
        ));
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
     * Compile the SQL needed to drop all views.
     *
     * @param  array  $views
     * @return string
     */
    public function compileDropAllViews($views)
    {
        return 'drop view '.implode(',', $this->wrapArray($views));
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
     * Compile the SQL needed to retrieve all view names.
     *
     * @return string
     */
    public function compileGetAllViews()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'VIEW\'';
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
     *
     * @return string
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
}
