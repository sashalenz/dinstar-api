<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Enums;

enum PortInfoType: string
{
    case TYPE = 'type';
    case IMEI = 'imei';
    case IMSI = 'imsi';
    case ICCID = 'iccid';
    case NUMBER = 'number';
    case REG = 'reg';
    case SLOT = 'slot';
    case CALL_STATE = 'callstate';
    case SIGNAL = 'signal';
    case GPRS = 'gprs';
    case SMSC = 'smsc';
    case CALL_FORWARD = 'CallForward';
    case REMAIN_CREDIT = 'remain_credit';
    case REMAIN_MONTHLY_CREDIT = 'remain_monthly_credit';
    case REMAIN_DAILY_CREDIT = 'remain_daily_credit';
    case REMAIN_DAILY_CALLTIME = 'remain_daily_calltime';
    case REMAIN_HOURLY_CALLTIME = 'remain_hourly_calltime';
    case REMAIN_DAILY_CONNECT = 'remain_daily_connect';
}
