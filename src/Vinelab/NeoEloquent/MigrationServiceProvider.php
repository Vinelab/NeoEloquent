<?php

namespace Vinelab\NeoEloquent;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Vinelab\NeoEloquent\Migrations\MigrationModel;
use Vinelab\NeoEloquent\Migrations\MigrationCreator;
use Vinelab\NeoEloquent\Console\Migrations\MigrateCommand;
use Vinelab\NeoEloquent\Console\Migrations\MigrateMakeCommand;
use Vinelab\NeoEloquent\Console\Migrations\MigrateResetCommand;
use Vinelab\NeoEloquent\Migrations\DatabaseMigrationRepository;
use Vinelab\NeoEloquent\Console\Migrations\MigrateRefreshCommand;
use Vinelab\NeoEloquent\Console\Migrations\MigrateRollbackCommand;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerRepository();

        // Once we have registered the migrator instance we will go ahead and register
        // all of the migration related commands that are used by the "Artisan" CLI
        // so that they may be easily accessed for registering with the consoles.
        $this->registerMigrator();

        $this->registerCommands();
    }

    /**
     * Register the migration repository service.
     */
    protected function registerRepository()
    {
        $this->app->bindShared('neoeloquent.migration.repository', function ($app) {
            $model = new MigrationModel();

            $label = $app['config']['database.migrations_node'];

            if (isset($label)) {
                $model->setLabel($label);
            }

            return new DatabaseMigrationRepository(
                $app['db'],
                $app['db']->connection('neo4j')->getSchemaBuilder(),
                $model
            );
        });
    }

    /**
     * Register the migrator service.
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->bindShared('neoeloquent.migrator', function ($app) {
            $repository = $app['neoeloquent.migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }

    /**
     * Register all of the migration commands.
     */
    protected function registerCommands()
    {
        $commands = array(
            'Migrate',
            'MigrateRollback',
            'MigrateReset',
            'MigrateRefresh',
            'MigrateMake',
        );

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        // Once the commands are registered in the application IoC container we will
        // register them with the Artisan start event so that these are available
        // when the Artisan application actually starts up and is getting used.
        $this->commands(
            'command.neoeloquent.migrate',
            'command.neoeloquent.migrate.make',
            'command.neoeloquent.migrate.rollback',
            'command.neoeloquent.migrate.reset',
            'command.neoeloquent.migrate.refresh'
        );
    }

    /**
     * Register the "migrate" migration command.
     */
    protected function registerMigrateCommand()
    {
        $this->app->bindShared('command.neoeloquent.migrate', function ($app) {
            $packagePath = $app['path.base'].'/vendor';

            return new MigrateCommand($app['neoeloquent.migrator'], $packagePath);
        });
    }

    /**
     * Register the "rollback" migration command.
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->bindShared('command.neoeloquent.migrate.rollback', function ($app) {
            return new MigrateRollbackCommand($app['neoeloquent.migrator']);
        });
    }

    /**
     * Register the "reset" migration command.
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->bindShared('command.neoeloquent.migrate.reset', function ($app) {
            return new MigrateResetCommand($app['neoeloquent.migrator']);
        });
    }

    /**
     * Register the "refresh" migration command.
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->bindShared('command.neoeloquent.migrate.refresh', function ($app) {
            return new MigrateRefreshCommand();
        });
    }

    /**
     * Register the "install" migration command.
     */
    protected function registerMigrateMakeCommand()
    {
        $this->app->bindShared('migration.neoeloquent.creator', function ($app) {
            return new MigrationCreator($app['files']);
        });

        $this->app->bindShared('command.neoeloquent.migrate.make', function ($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.neoeloquent.creator'];

            $packagePath = $app['path.base'].'/vendor';

            $composer = $app->make('Illuminate\Foundation\Composer');

            return new MigrateMakeCommand($creator, $composer, $packagePath);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return array(
            'neoeloquent.migrator',
            'neoeloquent.migration.repository',
            'command.neoeloquent.migrate',
            'command.neoeloquent.migrate.rollback',
            'command.neoeloquent.migrate.reset',
            'command.neoeloquent.migrate.refresh',
            'migration.neoeloquent.creator',
            'command.neoeloquent.migrate.make',
        );
    }
}
