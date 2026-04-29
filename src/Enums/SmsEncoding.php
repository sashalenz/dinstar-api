<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum SmsEncoding: string
{
    case UNICODE = 'unicode';
    case GSM_7BIT = 'gsm-7bit';
}
