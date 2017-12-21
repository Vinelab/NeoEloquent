<?php namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;

class MigrationCreator extends IlluminateMigrationCreator {

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string  $label
     * @return string
     */
    protected function populateStub($name, $stub, $label)
    {
        $stub = str_replace('DummyClass', studly_case($name), $stub);

        // Here we will replace the label place-holders with the label specified by
        // the developer, which is useful for quickly creating a labels creation
        // or update migration from the console instead of typing it manually.
        if ( ! is_null($label))
        {
            $stub = str_replace('DummyLabel', $label, $stub);
        }

        return $stub;
    }

    /**
     * {@inheritDoc}
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs';
    }

}
