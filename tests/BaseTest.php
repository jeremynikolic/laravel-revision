<?php

namespace Jeremynikolic\LaravelRevision\Tests;

use Orchestra\Testbench\TestCase;
use Jeremynikolic\LaravelRevision\LaravelRevisionServiceProvider;

class BaseTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelRevisionServiceProvider::class];
    }

}
