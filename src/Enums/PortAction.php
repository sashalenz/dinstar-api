<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum PortAction: string
{
    case SLOT = 'slot';
    case RESET = 'reset';
    case POWER = 'power';
    case IMEI = 'imei';
    case NUMBER = 'number';
    case LOCK = 'lock';
    case UNLOCK = 'unlock';
    case BLOCK = 'block';
    case UNBLOCK = 'unblock';
    case CALL_FORWARD = 'CallForward';
    case CHECK_CALL_FORWARD = 'CheckCallForward';
}
