<?php

namespace JeremyNikolic\Revision\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use JeremyNikolic\Revision\RevisionServiceProvider;

class TestCase extends OrchestraTestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->refreshDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [RevisionServiceProvider::class];
    }
}
