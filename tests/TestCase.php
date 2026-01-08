<?php

namespace Devpratyusa\UserDiscounts\Tests;

use Devpratyusa\UserDiscounts\UserDiscountsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

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
