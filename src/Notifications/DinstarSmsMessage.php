<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Notifications;

use Sashalenz\DinstarApi\Enums\SmsEncoding;

/**
 * Fluent message returned from a Notification's `toDinstarSms()` method.
 *
 * The message describes how the SMS should be sent — the recipient phone
 * number is supplied separately by the notifiable's
 * `routeNotificationForDinstarSms()` method.
 */
final class DinstarSmsMessage
{
    /**
     * @param  string  $text  Body text — may include "#param#" placeholders.
     * @param  array<int, int>|null  $ports  GSM ports (0–31). Null leaves the
     *                                       choice up to the gateway.
     * @param  SmsEncoding  $encoding  Default unicode (covers Cyrillic, emoji).
     * @param  bool  $requestStatusReport  Ask the carrier for a delivery
     *                                     receipt. Default true.
     * @param  int|null  $userId  Optional user id used to match the SMS in
     *                            later queries / pushed results.
     * @param  array<int, string>|null  $textParam  Values to substitute into
     *                                              "#param#" placeholders.
     */
    public function __construct(
        public string $text,
        public ?array $ports = null,
        public SmsEncoding $encoding = SmsEncoding::UNICODE,
        public bool $requestStatusReport = true,
        public ?int $userId = null,
        public ?array $textParam = null,
    ) {}

    public static function create(string $text = ''): self
    {
        return new self($text);
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param  array<int, int>  $ports
     */
    public function ports(array $ports): self
    {
        $this->ports = $ports;

        return $this;
    }

    public function encoding(SmsEncoding $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function unicode(): self
    {
        return $this->encoding(SmsEncoding::UNICODE);
    }

    public function gsm7bit(): self
    {
        return $this->encoding(SmsEncoding::GSM_7BIT);
    }

    public function withDeliveryReport(bool $request = true): self
    {
        $this->requestStatusReport = $request;

        return $this;
    }

    public function withoutDeliveryReport(): self
    {
        return $this->withDeliveryReport(false);
    }

    public function userId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param  array<int, string>  $params
     */
    public function textParam(array $params): self
    {
        $this->textParam = $params;

        return $this;
    }
}
