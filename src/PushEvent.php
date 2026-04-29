<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi;

use Sashalenz\DinstarApi\Exceptions\DinstarException;

/**
 * Parsed representation of an event pushed by the gateway to the user's web
 * server. The Dinstar gateway sends a single JSON object that always contains
 * an "sn" field plus exactly one payload field that names the event type.
 *
 * @see Documentation chapter 17 "Push Event"
 */
final class PushEvent
{
    public const TYPE_INCOMING_SMS = 'sms';

    public const TYPE_SMS_RESULT = 'sms_result';

    public const TYPE_SMS_DELIVER_STATUS = 'sms_deliver_status';

    public const TYPE_USSD = 'ussd';

    public const TYPE_REGISTER = 'register';

    public const TYPE_CDR = 'cdr';

    public const TYPE_DEVICE = 'device';

    public const TYPE_EXCEPTION = 'exception_info';

    private const KNOWN_TYPES = [
        self::TYPE_INCOMING_SMS,
        self::TYPE_SMS_RESULT,
        self::TYPE_SMS_DELIVER_STATUS,
        self::TYPE_USSD,
        self::TYPE_REGISTER,
        self::TYPE_CDR,
        self::TYPE_DEVICE,
        self::TYPE_EXCEPTION,
    ];

    public function __construct(
        public readonly string $sn,
        public readonly string $type,
        public readonly array $data,
    ) {}

    /**
     * Build a PushEvent from a decoded payload (associative array) or the raw
     * JSON body as it arrives from the gateway.
     *
     * @throws DinstarException when the payload cannot be recognised.
     */
    public static function fromPayload(array|string $payload): self
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (! is_array($decoded)) {
                throw DinstarException::malformed('push', 'payload is not valid JSON');
            }

            $payload = $decoded;
        }

        $sn = $payload['sn'] ?? null;

        if (! is_string($sn) || $sn === '') {
            throw DinstarException::malformed('push', 'missing "sn" field');
        }

        foreach (self::KNOWN_TYPES as $type) {
            if (array_key_exists($type, $payload)) {
                return new self(
                    sn: $sn,
                    type: $type,
                    data: (array) $payload[$type],
                );
            }
        }

        throw DinstarException::malformed('push', 'no recognised event field present');
    }

    public function isIncomingSms(): bool
    {
        return $this->type === self::TYPE_INCOMING_SMS;
    }

    public function isSmsResult(): bool
    {
        return $this->type === self::TYPE_SMS_RESULT;
    }

    public function isDeliverStatus(): bool
    {
        return $this->type === self::TYPE_SMS_DELIVER_STATUS;
    }

    public function isUssd(): bool
    {
        return $this->type === self::TYPE_USSD;
    }

    public function isRegister(): bool
    {
        return $this->type === self::TYPE_REGISTER;
    }

    public function isCdr(): bool
    {
        return $this->type === self::TYPE_CDR;
    }

    public function isDevice(): bool
    {
        return $this->type === self::TYPE_DEVICE;
    }

    public function isException(): bool
    {
        return $this->type === self::TYPE_EXCEPTION;
    }
}
