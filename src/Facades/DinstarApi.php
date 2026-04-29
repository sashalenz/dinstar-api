<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Sashalenz\DinstarApi\DinstarApi for(string $url, ?string $username = null, ?string $password = null)
 * @method static Collection sendSms(string $text, array $recipients, ?array $ports = null, \Sashalenz\DinstarApi\Enums\SmsEncoding $encoding = \Sashalenz\DinstarApi\Enums\SmsEncoding::UNICODE, bool $requestStatusReport = true)
 * @method static Collection querySmsResult(?array $numbers = null, ?array $ports = null, \DateTimeInterface|string|null $timeAfter = null, \DateTimeInterface|string|null $timeBefore = null, ?array $userIds = null)
 * @method static Collection querySmsDeliverStatus(?array $numbers = null, ?array $ports = null, \DateTimeInterface|string|null $timeAfter = null, \DateTimeInterface|string|null $timeBefore = null)
 * @method static Collection querySmsInQueue()
 * @method static Collection queryIncomingSms(?int $incomingSmsId = null, \Sashalenz\DinstarApi\Enums\IncomingSmsFlag $flag = \Sashalenz\DinstarApi\Enums\IncomingSmsFlag::UNREAD, ?array $ports = null)
 * @method static Collection stopSms(int $taskId)
 * @method static Collection sendUssd(array $ports, string $text, \Sashalenz\DinstarApi\Enums\UssdCommand $command = \Sashalenz\DinstarApi\Enums\UssdCommand::SEND)
 * @method static Collection queryUssdReply(array $ports)
 * @method static Collection getPortInfo(array $infoTypes, ?array $ports = null)
 * @method static Collection setPortInfo(int $port, \Sashalenz\DinstarApi\Enums\PortAction $action, ?string $param = null)
 * @method static Collection setCallForward(int $port, \Sashalenz\DinstarApi\Enums\CallForwardType $type, ?string $number = null)
 * @method static Collection checkCallForward(int $port)
 * @method static Collection getCallForward(int $port)
 * @method static Collection getCdr(?array $ports = null, \DateTimeInterface|string|null $timeAfter = null, \DateTimeInterface|string|null $timeBefore = null)
 * @method static Collection getStkView(int $port)
 * @method static Collection stkGo(int $port, ?int $item = null, ?string $param = null, ?\Sashalenz\DinstarApi\Enums\StkAction $action = null)
 * @method static Collection getStkCurrFrameIndex(int $port)
 * @method static Collection getStatus(array $sections = ['performance'])
 *
 * @see \Sashalenz\DinstarApi\DinstarApi
 */
class DinstarApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sashalenz\DinstarApi\DinstarApi::class;
    }
}
