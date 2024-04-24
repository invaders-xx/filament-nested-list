<?php

namespace InvadersXX\FilamentNestedList\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use InvadersXX\FilamentNestedList\FilamentNestedListServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'InvadersXX\\FilamentNestedList\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_filament-nested-list_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentNestedListServiceProvider::class,
        ];
    }
}
