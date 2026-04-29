# Changelog

All notable changes to `dinstar-api` will be documented in this file.

## Unreleased

### Changed
- Full rewrite of the package against the Dinstar GSM Gateway HTTP API
  document v202011.
- Bumped requirements to PHP `^8.2` and Laravel `^11.0 | ^12.0`.

### Added
- Dedicated methods for every documented API endpoint: `sendSms`,
  `querySmsResult`, `querySmsDeliverStatus`, `querySmsInQueue`,
  `queryIncomingSms`, `stopSms`, `sendUssd`, `queryUssdReply`,
  `getPortInfo`, `setPortInfo`, `setCallForward`, `checkCallForward`,
  `getCallForward`, `getCdr`, `getStkView`, `stkGo`,
  `getStkCurrFrameIndex`, `getStatus`.
- Backed enums for SMS encoding, status, USSD command, port action,
  port info type, register status, call forward type, STK action and
  incoming SMS flag.
- `PushEvent` parser for inbound push payloads (incoming SMS, sending
  results, delivery status, USSD, register, CDR, device, exception).
- HTTP basic auth, configurable timeout / retries / SSL verification.
- Pest test suite with `Http::fake()` coverage for every endpoint and
  for the push parser.

## 1.0.0 - 202X-XX-XX

- initial release
