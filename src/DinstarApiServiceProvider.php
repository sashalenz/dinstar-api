<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi;

use Illuminate\Notifications\ChannelManager;
use Sashalenz\DinstarApi\Notifications\DinstarSmsChannel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DinstarApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('dinstar-api')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(DinstarApi::class, function ($app) {
            $config = $app['config']->get('dinstar-api');

            $url = $config['url'] ?? null;

            if (! is_string($url) || $url === '') {
                throw new \RuntimeException(
                    'DINSTAR_API_URL must be configured before resolving Sashalenz\\DinstarApi\\DinstarApi.',
                );
            }

            return new DinstarApi(
                url: $url,
                username: (string) ($config['username'] ?? 'admin'),
                password: (string) ($config['password'] ?? 'admin'),
                timeout: (int) ($config['timeout'] ?? 10),
                retryTimes: (int) ($config['retry_times'] ?? 3),
                retrySleep: (int) ($config['retry_sleep'] ?? 100),
                verify: (bool) ($config['verify'] ?? false),
            );
        });

        $this->app->alias(DinstarApi::class, 'dinstar-api');
    }

    public function packageBooted(): void
    {
        $this->app->afterResolving(ChannelManager::class, function (ChannelManager $manager) {
            $manager->extend(DinstarSmsChannel::ROUTE, fn ($app) => $app->make(DinstarSmsChannel::class));
        });
    }
}
