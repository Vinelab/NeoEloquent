<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;

class MigrationCreator extends IlluminateMigrationCreator
{
    /**
     * Populate the place-holders in the migration stub.
     *
     * @param string $stub
     * @param string $label
     *
     * @return string
     */
    protected function populateStub($stub, $table)
    {
        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (! is_null($table)) {
            $stub = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        return $stub;
    }

    /**
     * {@inheritDoc}
     */
    public function getStubPath()
    {
        return __DIR__.'/stubs';
    }
}
