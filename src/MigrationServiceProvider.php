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

class  MigrationServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot() {}

    /**
     * {@inheritDoc}
     */
    public function register(): void
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
     *
     */
    protected function registerRepository(): void
    {
        $this->app->singleton(
            abstract: 'neoeloquent.migration.repository',
            concrete: function($app) {
                $model = new MigrationModel();

                $label = $app['config']['database.migrations_node'];

                if(isset($label)) {
                    $model->setLabel(label: $label);
                }

                return new DatabaseMigrationRepository(
                    resolver: $app['db'],
                    schema  : $app['db']->connection('neo4j')->getSchemaBuilder(),
                    model   : $model,
                );
            },
        );
    }

    /**
     * Register the migrator service.
     *
     */
    protected function registerMigrator(): void
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton(
            abstract: 'neoeloquent.migrator',
            concrete: function($app) {
                $repository = $app['neoeloquent.migration.repository'];

                return new Migrator(repository: $repository, resolver: $app['db'], files: $app['files']);
            },
        );
    }


    /**
     * Register all of the migration commands.
     *
     */
    protected function registerCommands(): void
    {
        $commands = [
            'Migrate',
            'MigrateRollback',
            'MigrateReset',
            'MigrateRefresh',
            'MigrateMake',
        ];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach($commands as $command) {
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
            'command.neoeloquent.migrate.refresh',
        );
    }

    /**
     * Register the "migrate" migration command.
     *
     */
    protected function registerMigrateCommand(): void
    {
        $this->app->singleton(
            abstract: 'command.neoeloquent.migrate',
            concrete: function($app) {
                $packagePath = $app['path.base'].'/vendor';

                return new MigrateCommand(migrator: $app['neoeloquent.migrator'], packagePath: $packagePath);
            },
        );
    }

    /**
     * Register the "rollback" migration command.
     *
     */
    protected function registerMigrateRollbackCommand(): void
    {
        $this->app->singleton(
            abstract: 'command.neoeloquent.migrate.rollback',
            concrete: function($app) {
                return new MigrateRollbackCommand(migrator: $app['neoeloquent.migrator']);
            },
        );
    }

    /**
     * Register the "reset" migration command.
     *
     */
    protected function registerMigrateResetCommand(): void
    {
        $this->app->singleton(
            abstract: 'command.neoeloquent.migrate.reset',
            concrete: function($app) {
                return new MigrateResetCommand(migrator: $app['neoeloquent.migrator']);
            },
        );
    }

    /**
     * Register the "refresh" migration command.
     *
     */
    protected function registerMigrateRefreshCommand(): void
    {
        $this->app->singleton(
            abstract: 'command.neoeloquent.migrate.refresh',
            concrete: function($app) {
                return new MigrateRefreshCommand();
            },
        );
    }

    /**
     * Register the "install" migration command.
     *
     */
    protected function registerMigrateMakeCommand(): void
    {
        $this->app->singleton(
            abstract: 'migration.neoeloquent.creator',
            concrete: function($app) {
                return new MigrationCreator(files: $app['files'], customStubPath: $app->basePath('stubs'));
            },
        );

        $this->app->singleton(
            abstract: 'command.neoeloquent.migrate.make',
            concrete: function($app) {
                // Once we have the migration creator registered, we will create the command
                // and inject the creator. The creator is responsible for the actual file
                // creation of the migrations, and may be extended by these developers.
                $creator = $app['migration.neoeloquent.creator'];

                $packagePath = $app['path.base'].'/vendor';

                $composer = $app->make('Illuminate\Support\Composer');

                return new MigrateMakeCommand(creator: $creator, composer: $composer, packagePath: $packagePath);
            },
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provides(): array
    {
        return [
            'neoeloquent.migrator',
            'neoeloquent.migration.repository',
            'command.neoeloquent.migrate',
            'command.neoeloquent.migrate.rollback',
            'command.neoeloquent.migrate.reset',
            'command.neoeloquent.migrate.refresh',
            'migration.neoeloquent.creator',
            'command.neoeloquent.migrate.make',
        ];
    }

}
