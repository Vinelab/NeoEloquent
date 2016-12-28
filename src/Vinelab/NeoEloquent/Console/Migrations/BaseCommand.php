<?php namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Console\Command;

class BaseCommand extends Command {

    /**
     * Directory for Neo4j labels migrations.
     *
     * @var string
     */
    CONST LABELS_DIRECTORY = 'labels';

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        // First, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        if ($this->input->hasOption('path') && $this->option('path')) {
            return [$this->laravel->basePath().'/'.$this->option('path')];
        }

        // If the package is in the list of migration paths we received we will put
        // the migrations in that path. Otherwise, we will assume the package is
        // is in the package directories and will place them in that location.
        if ($this->input->hasOption('package') && $this->option('package')) {
            return $this->packagePath.'/'.$this->option('package').'/src/' . self::LABELS_DIRECTORY;
        }

        // Finally we will check for the workbench option, which is a shortcut into
        // specifying the full path for a "workbench" project. Workbenches allow
        // developers to develop packages along side a "standard" app install.
        if ($this->input->hasOption('bench') && $bench = $this->option('bench'))
        {
            $path = "/workbench/{$bench}/src/" . self::LABELS_DIRECTORY;

            return $this->laravel['path.base'].$path;
        }

        return $this->laravel['path.database'].'/'.self::LABELS_DIRECTORY;
    }

}
