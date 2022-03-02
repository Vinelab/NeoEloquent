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
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCommands();
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository(): void
    {
        $this->app->singleton('neoeloquent.migration.repository', function($app)
        {
            $model = new MigrationModel;

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
     *
     * @return void
     */
    protected function registerMigrator(): void
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('neoeloquent.migrator', function($app) {
            $repository = $app['neoeloquent.migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }


    /**
     * Register all of the migration commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $commands = [
            'Migrate',
            'MigrateRollback',
            'MigrateReset',
            'MigrateRefresh',
            'MigrateMake'
        ];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command)
        {
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
     *
     * @return void
     */
    protected function registerMigrateCommand(): void
    {
        $this->app->singleton('command.neoeloquent.migrate', function($app) {
            $packagePath = $app['path.base'].'/vendor';

            return new MigrateCommand($app['neoeloquent.migrator'], $packagePath);
        });
    }

    /**
     * Register the "rollback" migration command.
     *
     * @return void
     */
    protected function registerMigrateRollbackCommand(): void
    {
        $this->app->singleton('command.neoeloquent.migrate.rollback', function($app)
        {
            return new MigrateRollbackCommand($app['neoeloquent.migrator']);
        });
    }

    /**
     * Register the "reset" migration command.
     *
     * @return void
     */
    protected function registerMigrateResetCommand(): void
    {
        $this->app->singleton('command.neoeloquent.migrate.reset', function($app)
        {
            return new MigrateResetCommand($app['neoeloquent.migrator']);
        });
    }

    /**
     * Register the "refresh" migration command.
     *
     * @return void
     */
    protected function registerMigrateRefreshCommand(): void
    {
        $this->app->singleton('command.neoeloquent.migrate.refresh', function($app)
        {
            return new MigrateRefreshCommand();
        });
    }

    /**
     * Register the "install" migration command.
     *
     * @return void
     */
    protected function registerMigrateMakeCommand(): void
    {
        $this->app->singleton('migration.neoeloquent.creator', function($app) {
            return new MigrationCreator($app['files'], $app->basePath('stubs'));
        });

        $this->app->singleton('command.neoeloquent.migrate.make', function($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.neoeloquent.creator'];

            $packagePath = $app['path.base'].'/vendor';

            $composer = $app->make('Illuminate\Support\Composer');

            return new MigrateMakeCommand($creator, $composer, $packagePath);
        });
    }
}
