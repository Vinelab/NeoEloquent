<?php namespace Vinelab\NeoEloquent\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends BaseCommand {

    use ConfirmableTrait;

    /**
     * {@inheritDoc}
     */
    protected $name = 'neo4j:migrate';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Run the database migrations';

    /**
     * The migrator instance.
     *
     * @var \Vinelab\NeoEloquent\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The path to the packages directory (vendor).
     */
    protected $packagePath;

    /**
     * @param  \Vinelab\NeoEloquent\Migrations\Migrator  $migrator
     * @param  string  $packagePath
     * @return void
     */
    public function __construct(Migrator $migrator, $packagePath)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->packagePath = $packagePath;
    }

    /**
     * {@inheritDoc}
     */
    public function fire()
    {
        if ( ! $this->confirmToProceed())
        {
            return;
        }

        // The pretend option can be used for "simulating" the migration and grabbing
        // the SQL queries that would fire if the migration were to be run against
        // a database for real, which is helpful for double checking migrations.
        $pretend = $this->input->getOption('pretend');

        $path = $this->getMigrationPath();

        $this->migrator->setConnection($this->input->getOption('database'));
        $this->migrator->run($path, [
            'pretend' => $pretend,
            'step' => $this->input->getOption('step'),
        ]);

        // Once the migrator has run we will grab the note output and send it out to
        // the console screen, since the migrator itself functions without having
        // any instances of the OutputInterface contract passed into the class.
        foreach ($this->migrator->getNotes() as $note)
        {
            $this->output->writeln($note);
        }

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed'))
        {
            $this->call('db:seed', ['--force' => true]);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions()
    {
        return array(
            array('bench', null, InputOption::VALUE_OPTIONAL, 'The name of the workbench to migrate.', null),

            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),

            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'),

            array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files.', null),

            array('package', null, InputOption::VALUE_OPTIONAL, 'The package to migrate.', null),

            array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),

            array('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'),

            array('step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually.'),
        );
    }

}
