<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;
use Illuminate\Support\Str;

class MigrationCreator extends IlluminateMigrationCreator
{
    /**
     * @param  string       $stub
     * @param  null|string  $table
     *
     * @return string
     */
    protected function populateStub($stub, $table)
    {
        $stub = str_replace('{{class}}', Str::studly($table), $stub);

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if ($table !== null) {
            $stub = str_replace(
                ['{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        return $stub;
    }

    /**
     * @throws FileNotFoundException
     */
    protected function getStub($table, $create)
    {
        $customPath = $this->customStubPath;
        if ($table === null) {
            $stub = $this->files->exists(path: "$customPath/blank.stub")
                ? $customPath
                : $this->stubPath().'/blank.stub';
        } elseif ($create) {
            $stub = $this->files->exists(path: "$customPath/create.stub")
                ? $customPath
                : $this->stubPath().'/create.stub';
        } else {
            $stub = $this->files->exists(path: "$customPath/update.stub")
                ? $customPath
                : $this->stubPath().'/update.stub';
        }

        return $this->files->get($stub);
    }

    public function create($name, $path, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(\dirname($path));

        $this->files->put(
            $path, $this->populateStub($stub, $table ?? $name)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table, $path);

        return $path;
    }

    /**
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }
}
