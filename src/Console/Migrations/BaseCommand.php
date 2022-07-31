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
    const LABELS_DIRECTORY = 'labels';

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        $path = $this->input->getOption('path');

        // First, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        if (!is_null($path)) {
            return $this->laravel['path.base'].'/'.$path;
        }

        return $this->laravel['path.database'].'/'.self::LABELS_DIRECTORY;
    }
}
