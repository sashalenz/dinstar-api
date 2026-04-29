<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Sashalenz\DinstarApi\DinstarApi;

/**
 * Laravel notification channel that dispatches SMS through the Dinstar
 * gateway.
 *
 * Usage:
 *
 *     // App\Notifications\OrderShipped
 *     public function via(object $notifiable): array
 *     {
 *         return [DinstarSmsChannel::class];
 *     }
 *
 *     public function toDinstarSms(object $notifiable): DinstarSmsMessage|string
 *     {
 *         return DinstarSmsMessage::create('Your order has shipped.')
 *             ->ports([1, 2])
 *             ->withoutDeliveryReport();
 *     }
 *
 *     // App\Models\User
 *     public function routeNotificationForDinstarSms($notification)
 *     {
 *         return $this->phone; // string or array<int, string>
 *     }
 */
final class DinstarSmsChannel
{
    public const ROUTE = 'dinstar-sms';

    public function __construct(
        private readonly DinstarApi $api,
    ) {}

    /**
     * Send the notification. Returns null when the notification cannot be
     * dispatched (no recipient or no `toDinstarSms` method); returns the
     * gateway response Collection on success.
     */
    public function send(object $notifiable, Notification $notification): ?Collection
    {
        if (! method_exists($notification, 'toDinstarSms')) {
            return null;
        }

        $recipients = $this->resolveRecipients($notifiable, $notification);

        if ($recipients === []) {
            return null;
        }

        $message = $this->resolveMessage($notification, $notifiable);

        return $this->api->sendSms(
            text: $message->text,
            recipients: $this->buildRecipientPayload($recipients, $message),
            ports: $message->ports,
            encoding: $message->encoding,
            requestStatusReport: $message->requestStatusReport,
        );
    }

    /**
     * @return array<int, string>
     */
    private function resolveRecipients(object $notifiable, Notification $notification): array
    {
        $route = $notifiable->routeNotificationFor(self::ROUTE, $notification);

        if ($route === null || $route === '' || $route === []) {
            return [];
        }

        $values = is_array($route) ? $route : [$route];

        return array_values(array_filter(array_map(
            static fn ($value) => is_string($value) ? trim($value) : null,
            $values,
        )));
    }

    private function resolveMessage(Notification $notification, object $notifiable): DinstarSmsMessage
    {
        /** @var DinstarSmsMessage|string $message */
        $message = $notification->toDinstarSms($notifiable);

        return is_string($message)
            ? DinstarSmsMessage::create($message)
            : $message;
    }

    /**
     * @param  array<int, string>  $phones
     * @return array<int, array{number: string, user_id?: int, text_param?: array<int, string>}>
     */
    private function buildRecipientPayload(array $phones, DinstarSmsMessage $message): array
    {
        return array_map(
            static fn (string $phone) => array_filter([
                'number' => $phone,
                'user_id' => $message->userId,
                'text_param' => $message->textParam,
            ], static fn ($value) => $value !== null),
            $phones,
        );
    }
}
