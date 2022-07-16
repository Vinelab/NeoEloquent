<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;

class MigrationCreator extends IlluminateMigrationCreator
{
    /**
     * Populate the place-holders in the migration stub.
     *
     * @param string $stub
     * @param string $table
     *
     * @return string
     */
    protected function populateStub($stub, $table)
    {
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
