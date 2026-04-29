<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\DinstarApi;
use Sashalenz\DinstarApi\Enums\CallForwardType;
use Sashalenz\DinstarApi\Enums\PortAction;
use Sashalenz\DinstarApi\Enums\PortInfoType;

beforeEach(function () {
    $this->api = app(DinstarApi::class);
});

it('gets port info for given ports', function () {
    Http::fake([
        'gateway.local/api/get_port_info*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'info' => [],
        ]),
    ]);

    $this->api->getPortInfo(
        infoTypes: [PortInfoType::IMEI, PortInfoType::IMSI, 'iccid'],
        ports: [1, 2, 3],
    );

    Http::assertSent(function (Request $r) {
        return $r->method() === 'GET'
            && str_contains($r->url(), 'info_type=imei%2Cimsi%2Ciccid')
            && str_contains($r->url(), 'port=1%2C2%2C3');
    });
});

it('sets port info via GET parameters', function () {
    Http::fake([
        'gateway.local/api/set_port_info*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
        ]),
    ]);

    $this->api->setPortInfo(port: 1, action: PortAction::POWER, param: 'off');

    Http::assertSent(function (Request $r) {
        return $r->method() === 'GET'
            && str_contains($r->url(), 'port=1')
            && str_contains($r->url(), 'action=power')
            && str_contains($r->url(), 'param=off');
    });
});

it('sets call forward', function () {
    Http::fake([
        'gateway.local/api/set_port_info*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
        ]),
    ]);

    $this->api->setCallForward(8, CallForwardType::UNCONDITIONAL, '15013828917');

    Http::assertSent(function (Request $r) {
        return str_contains($r->url(), 'action=CallForward')
            && str_contains($r->url(), 'param=Unconditional')
            && str_contains($r->url(), 'number=15013828917');
    });
});

it('checks call forward', function () {
    Http::fake([
        'gateway.local/api/set_port_info*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
        ]),
    ]);

    $this->api->checkCallForward(8);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'action=CheckCallForward'));
});

it('reads back call forward via get_port_info', function () {
    Http::fake([
        'gateway.local/api/get_port_info*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'info' => [['port' => 8, 'CallForwarding' => ['Unconditional' => '15013828917']]],
        ]),
    ]);

    $response = $this->api->getCallForward(8);

    expect($response->get('info')[0]['CallForwarding']['Unconditional'])->toBe('15013828917');
});
