# Dinstar GSM Gateway API for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sashalenz/dinstar-api.svg?style=flat-square)](https://packagist.org/packages/sashalenz/dinstar-api)
[![Total Downloads](https://img.shields.io/packagist/dt/sashalenz/dinstar-api.svg?style=flat-square)](https://packagist.org/packages/sashalenz/dinstar-api)

A Laravel client for the **Dinstar GSM Gateway HTTP API** (the "new API",
firmware ≥ 1102). Implements every endpoint described in the official
[`Dinstar GSM Gateway HTTP API`](https://www.dinstar.com/) document
(v202011): SMS, USSD, port management, CDR, STK, device status, and the
push-event payloads that the gateway can send back to your application.

## Requirements

| Package | Version |
| --- | --- |
| PHP | `^8.3 \| ^8.4 \| ^8.5` |
| Laravel | `^11.0 \| ^12.0 \| ^13.0` |

## Installation

```bash
composer require sashalenz/dinstar-api
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="dinstar-api-config"
```

Then add the gateway credentials to your `.env`:

```dotenv
DINSTAR_API_URL=https://192.168.1.10
DINSTAR_API_USERNAME=admin
DINSTAR_API_PASSWORD=admin

# Optional
DINSTAR_API_TIMEOUT=10
DINSTAR_API_RETRY_TIMES=3
DINSTAR_API_RETRY_SLEEP=100
DINSTAR_API_VERIFY_SSL=false
```

Most Dinstar units ship with a self-signed certificate, so `DINSTAR_API_VERIFY_SSL`
defaults to `false`. Set it to `true` if you have installed a valid certificate.

> Make sure the **new-version API** is enabled on the gateway:
> *Mobile Configuration → Basic Configuration → API*.

## Usage

The service is registered as a singleton and is exposed through the
`DinstarApi` facade and the `Sashalenz\DinstarApi\DinstarApi` class.

```php
use Sashalenz\DinstarApi\Facades\DinstarApi;

$response = DinstarApi::sendSms('Hello!', [['number' => '+380501234567']]);
$response->get('task_id'); // 7
```

Every method returns an `Illuminate\Support\Collection` containing the raw
JSON response from the gateway. Gateway-side errors raise
`Sashalenz\DinstarApi\Exceptions\DinstarException`.

### Talking to multiple gateways

```php
DinstarApi::for('https://10.0.0.5', 'admin', 'secret')
    ->sendSms('Hi', [['number' => '+380501234567']]);
```

---

## API reference

### SMS

```php
use Sashalenz\DinstarApi\Enums\SmsEncoding;

DinstarApi::sendSms(
    text: '#param#',
    recipients: [
        ['number' => '+380501234567', 'text_param' => ['Bob'], 'user_id' => 1],
        ['number' => '+380939876543', 'text_param' => ['Alice'], 'user_id' => 2],
    ],
    ports: [2, 3],                 // optional
    encoding: SmsEncoding::UNICODE, // or SmsEncoding::GSM_7BIT
    requestStatusReport: true,
);
```

Other SMS endpoints:

```php
DinstarApi::querySmsResult(userIds: [1, 2]);
DinstarApi::querySmsDeliverStatus(numbers: ['+380501234567']);
DinstarApi::querySmsInQueue();
DinstarApi::queryIncomingSms(flag: \Sashalenz\DinstarApi\Enums\IncomingSmsFlag::ALL);
DinstarApi::stopSms(taskId: 7);
```

### USSD

```php
use Sashalenz\DinstarApi\Enums\UssdCommand;

DinstarApi::sendUssd(ports: [1, 2], text: '*125#');
DinstarApi::sendUssd(ports: [1], text: '', command: UssdCommand::CANCEL);
DinstarApi::queryUssdReply([1, 2]);
```

### Port / SIM management

```php
use Sashalenz\DinstarApi\Enums\PortAction;
use Sashalenz\DinstarApi\Enums\PortInfoType;

DinstarApi::getPortInfo(
    infoTypes: [PortInfoType::TYPE, PortInfoType::IMEI, PortInfoType::SIGNAL],
    ports: [1, 2, 3],
);

DinstarApi::setPortInfo(port: 1, action: PortAction::POWER, param: 'off');
DinstarApi::setPortInfo(port: 1, action: PortAction::RESET);
DinstarApi::setPortInfo(port: 1, action: PortAction::SLOT, param: '2');
```

### Call forwarding

```php
use Sashalenz\DinstarApi\Enums\CallForwardType;

DinstarApi::setCallForward(8, CallForwardType::UNCONDITIONAL, '15013828917');
DinstarApi::checkCallForward(8);
DinstarApi::getCallForward(8);
```

### CDR

```php
DinstarApi::getCdr(
    ports: [2, 3],
    timeAfter: '2024-01-01 00:00:00',
    timeBefore: now(),
);
```

### STK

```php
use Sashalenz\DinstarApi\Enums\StkAction;

DinstarApi::getStkView(0);
DinstarApi::stkGo(port: 7, item: 1);
DinstarApi::stkGo(port: 7, action: StkAction::CANCEL);
DinstarApi::getStkCurrFrameIndex(0);
```

### Device status

```php
DinstarApi::getStatus(); // ['performance']
```

---

## Push events

The gateway can be configured to push events (incoming SMS, sending results,
delivery receipts, USSD replies, register status, CDR, device events,
exceptions) to your application. Use `PushEvent` to parse them in a single
controller:

```php
use Sashalenz\DinstarApi\PushEvent;

Route::post('/dinstar/push', function (Request $request) {
    $event = PushEvent::fromPayload($request->getContent());

    match (true) {
        $event->isIncomingSms()    => IncomingSms::ingest($event->sn, $event->data),
        $event->isSmsResult()      => SmsResult::ingest($event->sn, $event->data),
        $event->isDeliverStatus()  => DeliverStatus::ingest($event->sn, $event->data),
        $event->isCdr()            => Cdr::ingest($event->sn, $event->data),
        default                    => null,
    };

    return response()->noContent();
});
```

---

## Error handling

```php
use Sashalenz\DinstarApi\Exceptions\DinstarException;

try {
    DinstarApi::sendSms('Hi', [['number' => '+380501234567']]);
} catch (DinstarException $e) {
    // $e->getCode()    -> the gateway error_code (550, 413, 500, …)
    // $e->endpoint     -> the API path that failed
    // $e->payload      -> the full decoded response (when available)
}
```

The package recognises the documented error codes (`200`, `202`, `400`,
`404`, `413`, `500`, `550`) and includes a human-readable description in
the exception message.

## Testing

```bash
composer test
```

The test suite uses [Pest](https://pestphp.com/) and Laravel's
`Http::fake()` to stub gateway responses, so it runs without any network
access.

## Code style

```bash
composer format
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## Credits

- [Oleksandr Petrovskyi](https://github.com/sashalenz)

## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
