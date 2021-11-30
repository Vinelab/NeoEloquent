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
     * @var MigrationCreator
     */
    protected MigrationCreator $creator;

    /**
     * The path to the packages directory (vendor).
     */
    protected string $packagePath;

    protected Composer $composer;

    public function __construct(MigrationCreator $creator, Composer $composer, string $packagePath)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->packagePath = $packagePath;
        $this->composer = $composer;
    }

    public function handle(): void
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
     */
    protected function writeMigration(string $name, string $label): void
    {
        $path = $this->getMigrationPath();

        $file = pathinfo($this->creator->create($name, $path, $label), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['bench', null, InputOption::VALUE_OPTIONAL, 'The workbench the migration belongs to.', null],

            ['create', null, InputOption::VALUE_OPTIONAL, 'The label schema to be created.'],

            ['package', null, InputOption::VALUE_OPTIONAL, 'The package the migration belongs to.', null],

            ['path', null, InputOption::VALUE_OPTIONAL, 'Where to store the migration.', null],

            ['label', null, InputOption::VALUE_OPTIONAL, 'The label to migrate.'],
        ];
    }
}
