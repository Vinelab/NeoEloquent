<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Vinelab\NeoEloquent\Schema\Builder as SchemaBuilder;
use Vinelab\NeoEloquent\Eloquent\Model;

class DatabaseMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * The database connection resolver instance.
     *
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The migration model.
     *
     * @var Model
     */
    protected $model;

    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * @param ConnectionResolverInterface $resolver
     * @param SchemaBuilder $schema
     * @param Model $model
     */
    public function __construct(ConnectionResolverInterface $resolver, SchemaBuilder $schema, Model $model)
    {
        $this->resolver = $resolver;
        $this->schema = $schema;
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getRan()
    {
        return $this->model->all()->lists('migration');
    }

    /**
     * Get list of migrations.
     *
     * @param  int  $steps
     * @return array
     */
    public function getMigrations($steps)
    {
        $query = $this->label()->where('batch', '>=', '1');

        return $query->orderBy('migration', 'desc')->take($steps)->get()->all();
    }

    /**
     * {@inheritDoc}
     */
    public function getLast()
    {
        return $this->model->whereBatch($this->getLastBatchNumber())->get()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function log($file, $batch)
    {
        $record = array('migration' => $file, 'batch' => $batch);

        $this->model->create($record);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($migration)
    {
        $this->model->where('migration', $migration->migration)->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastBatchNumber()
    {
        return $this->label()->max('batch');
    }

    /**
     * {@inheritDoc}
     */
    public function createRepository()
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function repositoryExists()
    {
        return $this->schema->hasLabel($this->getLabel());
    }

    /**
     * Get a query builder for the migration node (table).
     *
     * @return Builder
     */
    protected function label()
    {
        return $this->getConnection()->table(array($this->getLabel()));
    }

    /**
     * Get the connection resolver instance.
     *
     * @return ConnectionResolverInterface
     */
    public function getConnectionResolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve the database connection instance.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->resolver->connection($this->connection);
    }

    /**
     * {@inheritDoc}
     */
    public function setSource($name)
    {
        $this->connection = $name;
    }

    /**
     * Set migration models label.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->model->setLabel($label);
    }

    /**
     * Get migration models label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->model->getLabel();
    }

    /**
     * Set migration model.
     *
     * @param Model $model
     */
    public function setMigrationModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get migration model.
     *
     * @return Model
     */
    public function getMigrationModel()
    {
        return $this->model;
    }

    public function getMigrationBatches()
    {
        return $this->label()->orderBy('batch')
            ->orderBy('migration')
            ->get();
    }

    public function deleteRepository(): void
    {
        $this->label()->delete();
    }
}
