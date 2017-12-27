<?php


namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Console\Command;

class BaseCommand extends Command
{
    /**
     * Directory for Neo4j labels migrations.
     *
     * @var string
     */
    CONST LABELS_DIRECTORY = 'labels';

    /**
     * Get the path to the migration directory.
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        // Here, we will check to see if a path option has been defined. If it has we will
        // use the path relative to the root of the installation folder so our database
        // migrations may be run for any customized path from within the application.
        if ($this->input->hasOption('path') && $this->option('path')) {
            return collect($this->option('path'))->map(function ($path) {
                return $this->laravel->basePath().'/'.$path;
            })->all();
        }

        return [$this->getMigrationPath()];
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.self::LABELS_DIRECTORY;
    }
}
