<?php

declare(strict_types=1);

use Sashalenz\DinstarApi\Exceptions\DinstarException;
use Sashalenz\DinstarApi\PushEvent;

it('parses incoming SMS push event', function () {
    $payload = [
        'sn' => 'da00-0030-1901-2817',
        'sms' => [[
            'incoming_sms_id' => 1,
            'port' => 1,
            'number' => '6717',
            'text' => 'test',
        ]],
    ];

    $event = PushEvent::fromPayload($payload);

    expect($event->isIncomingSms())->toBeTrue()
        ->and($event->sn)->toBe('da00-0030-1901-2817')
        ->and($event->data[0]['number'])->toBe('6717');
});

it('parses sms_result push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'sms_result' => [['port' => 1, 'status' => 'DELIVERED']],
    ]);

    expect($event->isSmsResult())->toBeTrue();
});

it('parses deliver status push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'sms_deliver_status' => [['port' => 1, 'ref_id' => 215, 'status_code' => 0]],
    ]);

    expect($event->isDeliverStatus())->toBeTrue();
});

it('parses register status push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'register' => [['port' => 8, 'status' => 'up', 'slot' => 2]],
    ]);

    expect($event->isRegister())->toBeTrue()
        ->and($event->data[0]['slot'])->toBe(2);
});

it('parses device push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'device' => ['port_number' => 32, 'status' => 'power_on'],
    ]);

    expect($event->isDevice())->toBeTrue()
        ->and($event->data['status'])->toBe('power_on');
});

it('parses exception_info push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'exception_info' => ['port' => 0, 'type' => 'call_fail', 'action' => 'reset'],
    ]);

    expect($event->isException())->toBeTrue();
});

it('parses CDR push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'cdr' => [['port' => 2, 'duration' => 3]],
    ]);

    expect($event->isCdr())->toBeTrue();
});

it('parses USSD push event', function () {
    $event = PushEvent::fromPayload([
        'sn' => 'aaaa',
        'ussd' => [['port' => 1, 'text' => 'Thank you!']],
    ]);

    expect($event->isUssd())->toBeTrue();
});

it('accepts a JSON string', function () {
    $event = PushEvent::fromPayload(json_encode([
        'sn' => 'aaaa',
        'sms' => [],
    ]));

    expect($event->isIncomingSms())->toBeTrue();
});

it('rejects payload without sn', function () {
    PushEvent::fromPayload(['sms' => []]);
})->throws(DinstarException::class, 'missing "sn"');

it('rejects unknown payload', function () {
    PushEvent::fromPayload(['sn' => 'x', 'unknown' => []]);
})->throws(DinstarException::class, 'no recognised event field');

it('rejects invalid JSON string', function () {
    PushEvent::fromPayload('not-json');
})->throws(DinstarException::class, 'not valid JSON');
