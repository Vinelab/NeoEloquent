<?php

namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;

class MigrateRefreshCommand extends Command
{
    use ConfirmableTrait;


    protected $name = 'neo4j:migrate:refresh';

    protected $description = 'Reset and re-run all migrations';

    public function handle(): void
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $database = $this->input->getOption('database');

        $force = $this->input->getOption('force');

        $this->call('migrate:reset', array(
            '--database' => $database, '--force' => $force,
        ));

        // The refresh command is essentially just a brief aggregate of a few other of
        // the migration commands and just provides a convenient wrapper to execute
        // them in succession. We'll also see if we need to re-seed the database.
        $this->call('migrate', array(
            '--database' => $database, '--force' => $force,
        ));

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }
    }

    /**
     * Determine if the developer has requested database seeding.
     */
    protected function needsSeeding(): bool
    {
        return $this->option('seed') || $this->option('seeder');
    }

    /**
     * Run the database seeder command.
     */
    protected function runSeeder(string $database): void
    {
        $class = $this->option('seeder') ?: 'DatabaseSeeder';

        $this->call('db:seed', array('--database' => $database, '--class' => $class));
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],

            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],

            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder.'],
        ];
    }
}
