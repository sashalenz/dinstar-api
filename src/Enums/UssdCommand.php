<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum UssdCommand: string
{
    case SEND = 'send';
    case CANCEL = 'cancel';
}
