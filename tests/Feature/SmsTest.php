<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\DinstarApi;
use Sashalenz\DinstarApi\Enums\IncomingSmsFlag;
use Sashalenz\DinstarApi\Enums\SmsEncoding;
use Sashalenz\DinstarApi\Exceptions\DinstarException;

beforeEach(function () {
    $this->api = app(DinstarApi::class);
});

it('sends SMS with required parameters', function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa-bbbb-cccc-dddd',
            'sms_in_queue' => 1,
            'task_id' => 7,
        ]),
    ]);

    $response = $this->api->sendSms('hello', [['number' => '10086']]);

    expect($response->get('task_id'))->toBe(7)
        ->and($response->get('sms_in_queue'))->toBe(1);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://gateway.local/api/send_sms'
            && $request->header('Authorization')[0] === 'Basic '.base64_encode('admin:admin')
            && $request['text'] === 'hello'
            && $request['param'] === [['number' => '10086']]
            && $request['encoding'] === 'unicode'
            && $request['request_status_report'] === true
            && ! array_key_exists('port', $request->data());
    });
});

it('sends SMS with optional ports and gsm-7bit encoding', function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'sms_in_queue' => 2,
            'task_id' => 9,
        ]),
    ]);

    $this->api->sendSms(
        text: '#param#',
        recipients: [
            ['number' => '10086', 'text_param' => ['bj'], 'user_id' => 1],
            ['number' => '10086', 'text_param' => ['ye'], 'user_id' => 2],
        ],
        ports: [2, 3],
        encoding: SmsEncoding::GSM_7BIT,
        requestStatusReport: false,
    );

    Http::assertSent(function (Request $request) {
        return $request['port'] === [2, 3]
            && $request['encoding'] === 'gsm-7bit'
            && $request['request_status_report'] === false;
    });
});

it('throws DinstarException when error_code is not 200/202', function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 550,
            'sn' => 'aaaa',
        ]),
    ]);

    $this->api->sendSms('hi', [['number' => '10086']]);
})->throws(DinstarException::class, 'error_code 550');

it('queries SMS sending result by user IDs', function () {
    Http::fake([
        'gateway.local/api/query_sms_result' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'result' => [[
                'port' => 0,
                'user_id' => 1,
                'number' => '12351',
                'time' => '2014-12-21 12:06:01',
                'status' => 'SENT_OK',
                'count' => 3,
                'succ_count' => 3,
                'ref_id' => 12,
                'imsi' => '460004642148063',
            ]],
        ]),
    ]);

    $response = $this->api->querySmsResult(userIds: [1, 2]);

    expect($response->get('error_code'))->toBe(200)
        ->and($response->get('result')[0]['user_id'])->toBe(1);

    Http::assertSent(fn (Request $r) => $r['user_id'] === [1, 2]);
});

it('queries SMS deliver status with date filters', function () {
    Http::fake([
        'gateway.local/api/query_sms_deliver_status' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'result' => [],
        ]),
    ]);

    $this->api->querySmsDeliverStatus(
        numbers: ['12341234'],
        ports: [1, 2, 3],
        timeAfter: '2014-12-12 19:29:19',
        timeBefore: new DateTimeImmutable('2014-12-13 19:29:19'),
    );

    Http::assertSent(function (Request $r) {
        return $r['number'] === ['12341234']
            && $r['port'] === [1, 2, 3]
            && $r['time_after'] === '2014-12-12 19:29:19'
            && $r['time_before'] === '2014-12-13 19:29:19';
    });
});

it('queries the SMS queue size', function () {
    Http::fake([
        'gateway.local/api/query_sms_in_queue' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'in_queue' => 0,
        ]),
    ]);

    expect($this->api->querySmsInQueue()->get('in_queue'))->toBe(0);
    Http::assertSent(fn (Request $r) => $r->method() === 'GET');
});

it('queries incoming SMS as a GET with csv ports', function () {
    Http::fake([
        'gateway.local/api/query_incoming_sms*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
            'sms' => [],
            'read' => 0,
            'unread' => 0,
        ]),
    ]);

    $this->api->queryIncomingSms(
        incomingSmsId: 5,
        flag: IncomingSmsFlag::ALL,
        ports: [1, 2, 3],
    );

    Http::assertSent(function (Request $r) {
        return $r->method() === 'GET'
            && str_contains($r->url(), 'incoming_sms_id=5')
            && str_contains($r->url(), 'flag=all')
            && str_contains($r->url(), 'port=1%2C2%2C3');
    });
});

it('stops SMS task by id', function () {
    Http::fake([
        'gateway.local/api/stop_sms*' => Http::response([
            'error_code' => 200,
            'sn' => 'aaaa',
        ]),
    ]);

    $this->api->stopSms(42);

    Http::assertSent(fn (Request $r) => $r->method() === 'GET'
        && str_contains($r->url(), 'task_id=42'));
});
