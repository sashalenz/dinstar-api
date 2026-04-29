<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum CallForwardType: string
{
    case UNCONDITIONAL = 'Unconditional';
    case NO_REPLY = 'NoReply';
    case BUSY = 'Busy';
    case NOT_REACHABLE = 'Not_Reachable';
    case CANCEL_ALL = 'CancelAll';
}
