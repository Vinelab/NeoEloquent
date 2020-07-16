<?php namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Support\Traits\Macroable;
use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Database\Eloquent\FactoryBuilder;

class NeoFactoryBuilder extends FactoryBuilder
{
    /**
     * Create a collection of models and persist them to the database.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function create(array $attributes = [])
    {
        $results = $this->make($attributes);

        if ($results instanceof Model) {
            $this->store(collect([$results]));
        } else {
            $this->store($results);
        }

        return $results;
    }

    /**
     * Set the connection name on the results and store them.
     *
     * @param  \Illuminate\Support\Collection  $results
     * @return void
     */
    protected function store($results)
    {
        $results->each(function ($model) {
            if (! isset($this->connection)) {

                $model->setConnection($model->getConnectionName());
            }

            $model->save();
        });
    }
}
