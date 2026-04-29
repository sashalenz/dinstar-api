<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum IncomingSmsFlag: string
{
    case UNREAD = 'unread';
    case READ = 'read';
    case ALL = 'all';
}
