<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\Enums\SmsEncoding;
use Sashalenz\DinstarApi\Notifications\DinstarSmsChannel;
use Sashalenz\DinstarApi\Notifications\DinstarSmsMessage;

beforeEach(function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'sms_in_queue' => 1,
            'task_id' => 1,
        ]),
    ]);

    $this->channel = app(DinstarSmsChannel::class);
});

it('sends an SMS when the notification returns a string', function () {
    $notification = new class extends Notification
    {
        public function toDinstarSms(object $notifiable): string
        {
            return 'Plain string body';
        }
    };

    $notifiable = (new AnonymousNotifiable)->route(DinstarSmsChannel::ROUTE, '+380501234567');

    $response = $this->channel->send($notifiable, $notification);

    expect($response?->get('task_id'))->toBe(1);

    Http::assertSent(function (Request $r) {
        return $r->method() === 'POST'
            && $r['text'] === 'Plain string body'
            && $r['param'] === [['number' => '+380501234567']]
            && $r['encoding'] === 'unicode'
            && $r['request_status_report'] === true
            && ! array_key_exists('port', $r->data());
    });
});

it('honours a fluent DinstarSmsMessage', function () {
    $notification = new class extends Notification
    {
        public function toDinstarSms(object $notifiable): DinstarSmsMessage
        {
            return DinstarSmsMessage::create('Hi #param#')
                ->ports([1, 2])
                ->gsm7bit()
                ->withoutDeliveryReport()
                ->userId(42)
                ->textParam(['Bob']);
        }
    };

    $notifiable = (new AnonymousNotifiable)->route(DinstarSmsChannel::ROUTE, '+380501234567');

    $this->channel->send($notifiable, $notification);

    Http::assertSent(function (Request $r) {
        return $r['text'] === 'Hi #param#'
            && $r['port'] === [1, 2]
            && $r['encoding'] === 'gsm-7bit'
            && $r['request_status_report'] === false
            && $r['param'] === [[
                'number' => '+380501234567',
                'user_id' => 42,
                'text_param' => ['Bob'],
            ]];
    });
});

it('routes to multiple phone numbers when the notifiable returns an array', function () {
    $notification = new class extends Notification
    {
        public function toDinstarSms(object $notifiable): string
        {
            return 'Multi recipient';
        }
    };

    $notifiable = (new AnonymousNotifiable)->route(
        DinstarSmsChannel::ROUTE,
        ['+380501111111', '+380502222222', ' '],
    );

    $this->channel->send($notifiable, $notification);

    Http::assertSent(function (Request $r) {
        return $r['param'] === [
            ['number' => '+380501111111'],
            ['number' => '+380502222222'],
        ];
    });
});

it('returns null and does not call the API when notifiable has no route', function () {
    $notification = new class extends Notification
    {
        public function toDinstarSms(object $notifiable): string
        {
            return 'no recipient';
        }
    };

    $notifiable = new AnonymousNotifiable;

    $response = $this->channel->send($notifiable, $notification);

    expect($response)->toBeNull();
    Http::assertNothingSent();
});

it('returns null when the notification does not implement toDinstarSms', function () {
    $notification = new class extends Notification {};

    $notifiable = (new AnonymousNotifiable)->route(DinstarSmsChannel::ROUTE, '+380501234567');

    $response = $this->channel->send($notifiable, $notification);

    expect($response)->toBeNull();
    Http::assertNothingSent();
});

it('respects DinstarSmsMessage encoding helpers', function () {
    $message = DinstarSmsMessage::create('hi')->unicode();
    expect($message->encoding)->toBe(SmsEncoding::UNICODE);

    $message->gsm7bit();
    expect($message->encoding)->toBe(SmsEncoding::GSM_7BIT);

    $message->withDeliveryReport(false);
    expect($message->requestStatusReport)->toBeFalse();
});
