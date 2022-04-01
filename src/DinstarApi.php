<?php

namespace Sashalenz\DinstarApi;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\Exceptions\DinstarException;

final class DinstarApi
{

    private const TIMEOUT = 3;
    private const RETRY_TIMES = 3;
    private const RETRY_SLEEP = 100;

    /**
     * @throws DinstarException
     */
    private function request(string $url, bool $isPost = true): Collection
    {
        try {
            return Http::timeout(self::TIMEOUT)
                ->retry(
                    self::RETRY_TIMES,
                    self::RETRY_SLEEP
                )
                ->withHeaders([
                    'Accept' => 'application/json'
                ])
                ->when($isPost, fn ($req) => $req->asJson())
                ->post(config('dinstar-api.url'))
                ->throw()
                ->collect();
        } catch (RequestException $e) {
            throw new DinstarException('Request error. '.$e->getMessage());
        }
    }
}
