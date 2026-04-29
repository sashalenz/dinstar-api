<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum SmsStatus: string
{
    case FAILED = 'FAILED';
    case SENDING = 'SENDING';
    case SENT_OK = 'SENT_OK';
    case DELIVERED = 'DELIVERED';
}
