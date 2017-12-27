<?php


namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Support\Composer;
use Vinelab\NeoEloquent\Migrations\MigrationCreator;

class MigrateMakeCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'neo4j:make:migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--label= : The label to migrate.}
        {--path= : The location where the migration file should be created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * @param  \Vinelab\NeoEloquent\Migrations\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // It's possible for the developer to specify the labels to modify in this
        // schema operation. The developer may also specify if this label needs
        // to be freshly created so we can create the appropriate migrations.
        $name = trim($this->input->getArgument('name'));

        $label = $this->input->getOption('label');

        $create = $this->input->getOption('create') ?: false;

        // If no label was given as an option but a create option is given then we
        // will use the "create" option as the label name. This allows the devs
        // to pass a label name into this option as a short-cut for creating.
        if (! $label && is_string($create)) {
            $label = $create;
            $create = true;
        }

        // Next, we will attempt to guess the label name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new labels for the application.
        if (! $label) {
            if (preg_match('/^create_(\w+)_label$/', $name, $matches)) {
                $label = $matches[1];
                $create = true;
            }
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $label, $create);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  bool    $create
     * @return string
     */
    protected function writeMigration($name, $label, $create)
    {
        $file = pathinfo($this->creator->create(
            $name, $this->getMigrationPath(), $label, $create
        ), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return $this->laravel->basePath().'/'.$targetPath;
        }
        return parent::getMigrationPath();
    }
}
