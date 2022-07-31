<?php

namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Vinelab\NeoEloquent\Migrations\MigrationCreator;

class MigrateMakeCommand extends BaseCommand
{

    protected $signature = 'neo4j:make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--label= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';


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

    public function handle()
    {
        $name = $this->input->getArgument('name');

        $label = $this->input->getOption('label');

        $modify = $this->input->getOption('create') ?? false;

        if (!$label && is_string($modify)) {
            $label = $modify;
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $label, $modify);

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
    protected function writeMigration(string $name, ?string $label, bool $modify)
    {
        $file = pathinfo($this->creator->create(
            $name, $this->getMigrationPath(), $label, $modify
        ), PATHINFO_FILENAME);

        $this->components->info(sprintf('Created migration [%s].', $file));
    }

    protected function getMigrationPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        return parent::getMigrationPath();
    }

    protected function usingRealPath(): bool
    {
        return $this->input->hasOption('realpath') && $this->option('realpath');
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
}
