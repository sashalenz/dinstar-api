<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\DinstarApi;
use Sashalenz\DinstarApi\Enums\UssdCommand;

beforeEach(function () {
    $this->api = app(DinstarApi::class);
});

it('sends USSD code', function () {
    Http::fake([
        'gateway.local/api/send_ussd' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'result' => [
                ['port' => 0, 'status' => 503],
                ['port' => 1, 'status' => 200],
            ],
        ]),
    ]);

    $this->api->sendUssd(ports: [0, 1, 2], text: '*125#');

    Http::assertSent(function (Request $r) {
        return $r->method() === 'POST'
            && $r['port'] === [0, 1, 2]
            && $r['command'] === 'send'
            && $r['text'] === '*125#';
    });
});

it('cancels USSD with explicit command', function () {
    Http::fake([
        'gateway.local/api/send_ussd' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'result' => [],
        ]),
    ]);

    $this->api->sendUssd([1], '', UssdCommand::CANCEL);

    Http::assertSent(fn (Request $r) => $r['command'] === 'cancel');
});

it('queries USSD reply with csv ports', function () {
    Http::fake([
        'gateway.local/api/query_ussd_reply*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'reply' => [['port' => 1, 'text' => 'Thanks!']],
        ]),
    ]);

    $response = $this->api->queryUssdReply([1, 2, 3]);

    expect($response->get('reply')[0]['text'])->toBe('Thanks!');
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'port=1%2C2%2C3'));
});
