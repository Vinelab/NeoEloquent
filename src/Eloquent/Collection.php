<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection as IlluminateCollection;

class Collection extends IlluminateCollection
{
    /**
     * Find a model in the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($key, $default = null)
    {
        if ($key instanceof Model) {
            $key = $key->getKey();
        }

        return Arr::first($this->items, function ($itemKey, $model) use ($key) {
            return $model->getKey() == $key;

        }, $default);
    }

    /**
     * Determine if a key exists in the collection.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($key, $value = null)
    {
        if (func_num_args() == 2) {
            return parent::contains($key, $value);
        }

        if ($this->useAsCallable($key)) {
            return parent::contains($key);
        }

        $key = $key instanceof Model ? $key->getKey() : $key;

        return parent::contains(function ($k, $m) use ($key) {
            return $m->getKey() == $key;
        });
    }

    /**
     * Fetch a nested element of the collection.
     *
     * @param string $key
     *
     * @return static
     *
     * @deprecated since version 5.1. Use pluck instead.
     */
    public function fetch($key)
    {
        return new static(Arr::fetch($this->toArray(), $key));
    }
}
