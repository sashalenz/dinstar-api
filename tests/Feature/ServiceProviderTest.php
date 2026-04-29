<?php

declare(strict_types=1);

use Sashalenz\DinstarApi\DinstarApi;
use Sashalenz\DinstarApi\Facades\DinstarApi as DinstarApiFacade;

it('registers DinstarApi as a singleton', function () {
    expect(app(DinstarApi::class))->toBeInstanceOf(DinstarApi::class)
        ->and(app(DinstarApi::class))->toBe(app(DinstarApi::class));
});

it('aliases the singleton under the dinstar-api binding', function () {
    expect(app('dinstar-api'))->toBe(app(DinstarApi::class));
});

it('resolves the same instance through the facade', function () {
    expect(DinstarApiFacade::getFacadeRoot())->toBe(app(DinstarApi::class));
});

it('throws when the URL is not configured', function () {
    config()->set('dinstar-api.url', null);
    app()->forgetInstance(DinstarApi::class);

    app(DinstarApi::class);
})->throws(RuntimeException::class, 'DINSTAR_API_URL');

it('returns a separate instance from for() with new credentials', function () {
    $api = app(DinstarApi::class);
    $other = $api->for('https://other.local', 'root', 'secret');

    expect($other)->not->toBe($api)
        ->and($other)->toBeInstanceOf(DinstarApi::class);
});
