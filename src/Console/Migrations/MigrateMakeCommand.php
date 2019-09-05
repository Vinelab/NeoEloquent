<?php

namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Vinelab\NeoEloquent\Migrations\MigrationCreator;

class MigrateMakeCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'neo4j:make:migration';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Create a new migration file';

    /**
     * @var \Vinelab\NeoEloquent\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The path to the packages directory (vendor).
     *
     * @var string
     */
    protected $packagePath;

    /**
     * @var \Illuminate\Foundation\Composer
     */
    protected $composer;

    /**
     * @param \Vinelab\NeoEloquent\Migrations\MigrationCreator $creator
     * @param string                                           $packagePath
     */
    public function __construct(MigrationCreator $creator, Composer $composer, $packagePath)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->packagePath = $packagePath;
        $this->composer = $composer;
    }

    /**
     * {@inheritDoc}
     */
    public function fire()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this label needs
        // to be freshly created so we can create the appropriate migrations.
        $name = $this->input->getArgument('name');

        $label = $this->input->getOption('label');

        $modify = $this->input->getOption('create');

        if (!$label && is_string($modify)) {
            $label = $modify;
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $label);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $label
     * @param bool   $create
     *
     * @return string
     */
    protected function writeMigration($name, $label)
    {
        $path = $this->getMigrationPath();

        $file = pathinfo($this->creator->create($name, $path, $label), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }

    /**
     * {@inheritDoc}
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'The name of the migration'),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions()
    {
        return array(
            array('bench', null, InputOption::VALUE_OPTIONAL, 'The workbench the migration belongs to.', null),

            array('create', null, InputOption::VALUE_OPTIONAL, 'The label schema to be created.'),

            array('package', null, InputOption::VALUE_OPTIONAL, 'The package the migration belongs to.', null),

            array('path', null, InputOption::VALUE_OPTIONAL, 'Where to store the migration.', null),

            array('label', null, InputOption::VALUE_OPTIONAL, 'The label to migrate.'),
        );
    }
}
