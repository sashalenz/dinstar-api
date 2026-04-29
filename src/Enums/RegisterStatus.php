<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum RegisterStatus: string
{
    case POWER_OFF = 'POWER_OFF';
    case NO_SIM = 'NO_SIM';
    case PIN_REQUIRE = 'PIN_REQUIRE';
    case PUK_REQUIRE = 'PUK_REQUIRE';
    case UNREGISTER = 'UNREGISTER';
    case SEARCHING_NETWORK = 'SEARCHING_NETWORK';
    case REGISTER_OK = 'REGISTER_OK';
    case UNKNOWN = 'UNKNOWN';
}
