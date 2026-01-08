<?php

namespace Devpratyusa\UserDiscounts\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Devpratyusa\UserDiscounts\UserDiscountsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [UserDiscountsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }
}