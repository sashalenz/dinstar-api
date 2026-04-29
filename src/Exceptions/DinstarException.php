<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Exceptions;

use Exception;
use Throwable;

class DinstarException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?string $endpoint = null,
        public readonly array $payload = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function transport(string $endpoint, Throwable $previous): self
    {
        return new self(
            message: sprintf('Dinstar request to "%s" failed: %s', $endpoint, $previous->getMessage()),
            code: (int) $previous->getCode(),
            previous: $previous,
            endpoint: $endpoint,
        );
    }

    public static function api(string $endpoint, int $errorCode, array $payload = []): self
    {
        return new self(
            message: sprintf(
                'Dinstar API "%s" returned error_code %d (%s).',
                $endpoint,
                $errorCode,
                self::describe($errorCode),
            ),
            code: $errorCode,
            endpoint: $endpoint,
            payload: $payload,
        );
    }

    public static function malformed(string $endpoint, string $reason): self
    {
        return new self(
            message: sprintf('Dinstar API "%s" returned a malformed response: %s', $endpoint, $reason),
            endpoint: $endpoint,
        );
    }

    private static function describe(int $code): string
    {
        return match ($code) {
            400 => 'Request format is not valid',
            404 => 'Resource (e.g. SMS task) not found',
            413 => 'Payload too large (e.g. number of recipients or text length exceeded)',
            500 => 'Other gateway error',
            550 => 'No available port for sending SMS',
            default => 'Unexpected error',
        };
    }
}
