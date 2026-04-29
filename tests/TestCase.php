<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sashalenz\DinstarApi\DinstarApiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DinstarApiServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('dinstar-api.url', 'https://gateway.local');
        $app['config']->set('dinstar-api.username', 'admin');
        $app['config']->set('dinstar-api.password', 'admin');
        $app['config']->set('dinstar-api.timeout', 1);
        $app['config']->set('dinstar-api.retry_times', 1);
        $app['config']->set('dinstar-api.retry_sleep', 0);
        $app['config']->set('dinstar-api.verify', false);
    }
}
