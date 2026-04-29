<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\DinstarApi;
use Sashalenz\DinstarApi\Enums\StkAction;

beforeEach(function () {
    $this->api = app(DinstarApi::class);
});

it('gets CDR records as POST', function () {
    Http::fake([
        'gateway.local/api/get_cdr' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'cdr' => [[
                'port' => 2,
                'start_date' => '2015-07-21 16:35:20',
                'destination_number' => '6717',
                'direction' => 'ip->gsm',
            ]],
        ]),
    ]);

    $this->api->getCdr(ports: [2, 3], timeAfter: '2015-07-01 00:00:00');

    Http::assertSent(function (Request $r) {
        return $r->method() === 'POST'
            && $r['port'] === [2, 3]
            && $r['time_after'] === '2015-07-01 00:00:00'
            && ! array_key_exists('time_before', $r->data());
    });
});

it('reads device performance status', function () {
    Http::fake([
        'gateway.local/api/get_status' => Http::response([
            'performance' => [
                'cpu_used' => '39',
                'flash_total' => '27648',
                'flash_used' => '17428',
                'memory_total' => '109448',
                'memory_cached' => '34192',
                'memory_buffers' => '0',
                'memory_free' => '58928',
                'memory_used' => '50520',
            ],
        ]),
    ]);

    $response = $this->api->getStatus();

    expect($response->get('performance')['cpu_used'])->toBe('39');

    Http::assertSent(function (Request $r) {
        return $r->method() === 'POST'
            && $r->data() === ['performance'];
    });
});

it('queries STK view as raw GET', function () {
    Http::fake([
        'gateway.local/GetSTKView*' => Http::response([
            'title' => 'Test',
            'item' => [['item_id' => 1, 'item_string' => 'option']],
            'input_type' => 2,
            'frame_id' => 750,
        ]),
    ]);

    $response = $this->api->getStkView(0);

    expect($response->get('frame_id'))->toBe(750);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'port=0'));
});

it('navigates STK menu via POST', function () {
    Http::fake([
        'gateway.local/STKGo' => Http::response([]),
    ]);

    $this->api->stkGo(port: 7, item: 1);
    $this->api->stkGo(port: 7, action: StkAction::CANCEL);

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->url() === 'https://gateway.local/STKGo');
});

it('reads STK current frame index', function () {
    Http::fake([
        'gateway.local/GetSTKCurrFrameIndex*' => Http::response(['frame_id' => 32]),
    ]);

    expect($this->api->getStkCurrFrameIndex(0)->get('frame_id'))->toBe(32);
});
