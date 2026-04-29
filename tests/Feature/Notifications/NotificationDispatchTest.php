<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Sashalenz\DinstarApi\Notifications\DinstarSmsChannel;
use Sashalenz\DinstarApi\Notifications\DinstarSmsMessage;

it('dispatches a notification end-to-end via the channel class', function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'sms_in_queue' => 1,
            'task_id' => 99,
        ]),
    ]);

    $notification = new class extends Notification
    {
        public function via(object $notifiable): array
        {
            return [DinstarSmsChannel::class];
        }

        public function toDinstarSms(object $notifiable): DinstarSmsMessage
        {
            return DinstarSmsMessage::create('Order shipped');
        }
    };

    NotificationFacade::route(DinstarSmsChannel::ROUTE, '+380501234567')
        ->notify($notification);

    Http::assertSent(function (Request $r) {
        return $r['text'] === 'Order shipped'
            && $r['param'] === [['number' => '+380501234567']];
    });
});

it('dispatches a notification through the dinstar-sms string alias', function () {
    Http::fake([
        'gateway.local/api/send_sms' => Http::response([
            'error_code' => 202,
            'sn' => 'aaaa',
            'sms_in_queue' => 1,
            'task_id' => 100,
        ]),
    ]);

    $notification = new class extends Notification
    {
        public function via(object $notifiable): array
        {
            return ['dinstar-sms'];
        }

        public function toDinstarSms(object $notifiable): string
        {
            return 'Hello via alias';
        }
    };

    NotificationFacade::route('dinstar-sms', '+380501234567')
        ->notify($notification);

    Http::assertSent(fn (Request $r) => $r['text'] === 'Hello via alias');
});
