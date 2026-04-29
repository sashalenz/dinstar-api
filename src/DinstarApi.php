<?php

declare(strict_types=1);

namespace Sashalenz\DinstarApi;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Sashalenz\DinstarApi\Enums\CallForwardType;
use Sashalenz\DinstarApi\Enums\IncomingSmsFlag;
use Sashalenz\DinstarApi\Enums\PortAction;
use Sashalenz\DinstarApi\Enums\PortInfoType;
use Sashalenz\DinstarApi\Enums\SmsEncoding;
use Sashalenz\DinstarApi\Enums\StkAction;
use Sashalenz\DinstarApi\Enums\UssdCommand;
use Sashalenz\DinstarApi\Exceptions\DinstarException;

/**
 * Laravel client for the Dinstar GSM Gateway HTTP API.
 *
 * Every public method maps 1:1 to an endpoint described in the Dinstar
 * "Instructions for Using New API" document (v202011). Successful responses
 * are returned as Illuminate Collections; gateway errors raise
 * {@see DinstarException}.
 */
final class DinstarApi
{
    private const SUCCESS_CODES = [200, 202];

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private string $url,
        private string $username,
        private string $password,
        private int $timeout = 10,
        private int $retryTimes = 3,
        private int $retrySleep = 100,
        private bool $verify = false,
    ) {
        $this->url = rtrim($url, '/');
    }

    /**
     * Build a one-off client for a specific gateway. Useful when an
     * application talks to multiple gateways from the same process.
     */
    public function for(string $url, ?string $username = null, ?string $password = null): self
    {
        return new self(
            url: $url,
            username: $username ?? $this->username,
            password: $password ?? $this->password,
            timeout: $this->timeout,
            retryTimes: $this->retryTimes,
            retrySleep: $this->retrySleep,
            verify: $this->verify,
        );
    }

    /* -------------------------------------------------------------------
     |  SMS
     | -------------------------------------------------------------------
     */

    /**
     * Send one or more SMS messages.
     *
     * @param  string  $text  Body text — may contain "#param#" placeholders.
     * @param  array<int, array{number: string, text_param?: array<int,string>, user_id?: int}>  $recipients
     * @param  array<int, int>|null  $ports  GSM ports (0–31) to use.
     */
    public function sendSms(
        string $text,
        array $recipients,
        ?array $ports = null,
        SmsEncoding $encoding = SmsEncoding::UNICODE,
        bool $requestStatusReport = true,
    ): Collection {
        return $this->post('/api/send_sms', array_filter([
            'text' => $text,
            'param' => array_values($recipients),
            'port' => $ports,
            'encoding' => $encoding->value,
            'request_status_report' => $requestStatusReport,
        ], static fn ($v) => $v !== null));
    }

    /**
     * Query the result of an SMS sending task.
     *
     * @param  array<int, string>|null  $numbers
     * @param  array<int, int>|null  $ports
     * @param  array<int, int>|null  $userIds
     */
    public function querySmsResult(
        ?array $numbers = null,
        ?array $ports = null,
        DateTimeInterface|string|null $timeAfter = null,
        DateTimeInterface|string|null $timeBefore = null,
        ?array $userIds = null,
    ): Collection {
        return $this->post('/api/query_sms_result', array_filter([
            'number' => $numbers,
            'port' => $ports,
            'time_after' => $this->formatDate($timeAfter),
            'time_before' => $this->formatDate($timeBefore),
            'user_id' => $userIds,
        ], static fn ($v) => $v !== null));
    }

    /**
     * Query SMS delivery status (delivery receipts received from the carrier).
     *
     * @param  array<int, string>|null  $numbers
     * @param  array<int, int>|null  $ports
     */
    public function querySmsDeliverStatus(
        ?array $numbers = null,
        ?array $ports = null,
        DateTimeInterface|string|null $timeAfter = null,
        DateTimeInterface|string|null $timeBefore = null,
    ): Collection {
        return $this->post('/api/query_sms_deliver_status', array_filter([
            'number' => $numbers,
            'port' => $ports,
            'time_after' => $this->formatDate($timeAfter),
            'time_before' => $this->formatDate($timeBefore),
        ], static fn ($v) => $v !== null));
    }

    /**
     * Query the number of SMS messages still queued for delivery.
     */
    public function querySmsInQueue(): Collection
    {
        return $this->get('/api/query_sms_in_queue');
    }

    /**
     * Receive incoming SMS messages.
     *
     * @param  array<int, int>|null  $ports
     */
    public function queryIncomingSms(
        ?int $incomingSmsId = null,
        IncomingSmsFlag $flag = IncomingSmsFlag::UNREAD,
        ?array $ports = null,
    ): Collection {
        return $this->get('/api/query_incoming_sms', array_filter([
            'incoming_sms_id' => $incomingSmsId,
            'flag' => $flag->value,
            'port' => $this->csv($ports),
        ], static fn ($v) => $v !== null));
    }

    /**
     * Stop a running SMS sending task identified by the task_id returned from
     * {@see sendSms()}.
     */
    public function stopSms(int $taskId): Collection
    {
        return $this->get('/api/stop_sms', ['task_id' => $taskId]);
    }

    /* -------------------------------------------------------------------
     |  USSD
     | -------------------------------------------------------------------
     */

    /**
     * Send a USSD code (e.g. "*125#") through one or more ports.
     *
     * @param  array<int, int>  $ports
     */
    public function sendUssd(
        array $ports,
        string $text,
        UssdCommand $command = UssdCommand::SEND,
    ): Collection {
        return $this->post('/api/send_ussd', [
            'port' => $ports,
            'command' => $command->value,
            'text' => $text,
        ]);
    }

    /**
     * Query USSD replies for the given ports.
     *
     * @param  array<int, int>  $ports
     */
    public function queryUssdReply(array $ports): Collection
    {
        return $this->get('/api/query_ussd_reply', ['port' => $this->csv($ports)]);
    }

    /* -------------------------------------------------------------------
     |  Port / SIM management
     | -------------------------------------------------------------------
     */

    /**
     * Get information about one or more ports.
     *
     * @param  array<int, PortInfoType|string>  $infoTypes
     * @param  array<int, int>|null  $ports
     */
    public function getPortInfo(array $infoTypes, ?array $ports = null): Collection
    {
        return $this->get('/api/get_port_info', array_filter([
            'info_type' => $this->csv(array_map($this->enumValue(...), $infoTypes)),
            'port' => $this->csv($ports),
        ], static fn ($v) => $v !== null));
    }

    /**
     * Set port configuration: power, reset, slot, IMEI, lock/unlock, etc.
     */
    public function setPortInfo(int $port, PortAction $action, ?string $param = null): Collection
    {
        return $this->get('/api/set_port_info', array_filter([
            'port' => $port,
            'action' => $action->value,
            'param' => $param,
        ], static fn ($v) => $v !== null));
    }

    /**
     * Configure call forwarding on a port.
     */
    public function setCallForward(int $port, CallForwardType $type, ?string $number = null): Collection
    {
        return $this->get('/api/set_port_info', array_filter([
            'port' => $port,
            'action' => PortAction::CALL_FORWARD->value,
            'param' => $type->value,
            'number' => $number,
        ], static fn ($v) => $v !== null));
    }

    /**
     * Send the "check call forward" command to the carrier. Combine with
     * {@see getCallForward()} once the carrier replies.
     */
    public function checkCallForward(int $port): Collection
    {
        return $this->get('/api/set_port_info', [
            'port' => $port,
            'action' => PortAction::CHECK_CALL_FORWARD->value,
        ]);
    }

    /**
     * Read the call forward configuration cached by the gateway.
     */
    public function getCallForward(int $port): Collection
    {
        return $this->get('/api/get_port_info', [
            'port' => $port,
            'info_type' => PortInfoType::CALL_FORWARD->value,
        ]);
    }

    /* -------------------------------------------------------------------
     |  CDR
     | -------------------------------------------------------------------
     */

    /**
     * Get Call Detail Records.
     *
     * @param  array<int, int>|null  $ports
     */
    public function getCdr(
        ?array $ports = null,
        DateTimeInterface|string|null $timeAfter = null,
        DateTimeInterface|string|null $timeBefore = null,
    ): Collection {
        return $this->post('/api/get_cdr', array_filter([
            'port' => $ports,
            'time_after' => $this->formatDate($timeAfter),
            'time_before' => $this->formatDate($timeBefore),
        ], static fn ($v) => $v !== null));
    }

    /* -------------------------------------------------------------------
     |  STK
     | -------------------------------------------------------------------
     */

    /**
     * Read the current STK menu shown on a port. Note: the STK endpoints
     * return raw JSON (without an error_code field).
     */
    public function getStkView(int $port): Collection
    {
        return $this->getRaw('/GetSTKView', ['port' => $port]);
    }

    /**
     * Navigate the STK menu on a port (select an item, type a value, cancel,
     * go home, etc.).
     */
    public function stkGo(
        int $port,
        ?int $item = null,
        ?string $param = null,
        ?StkAction $action = null,
    ): Collection {
        return $this->postRaw('/STKGo', array_filter([
            'port' => $port,
            'item' => $item,
            'param' => $param,
            'action' => $action?->value,
        ], static fn ($v) => $v !== null));
    }

    /**
     * Read the current STK frame index for a port.
     */
    public function getStkCurrFrameIndex(int $port): Collection
    {
        return $this->getRaw('/GetSTKCurrFrameIndex', ['port' => $port]);
    }

    /* -------------------------------------------------------------------
     |  Device status
     | -------------------------------------------------------------------
     */

    /**
     * Get device performance information (CPU / memory / flash usage).
     * The payload is an envelope with the requested keys, by default
     * "performance".
     *
     * @param  array<int, string>  $sections
     */
    public function getStatus(array $sections = ['performance']): Collection
    {
        return $this->postRaw('/api/get_status', array_values($sections));
    }

    /* -------------------------------------------------------------------
     |  Internals
     | -------------------------------------------------------------------
     */

    /**
     * GET request that asserts a successful error_code in the response.
     */
    private function get(string $endpoint, array $query = []): Collection
    {
        $response = $this->send('GET', $endpoint, query: $query);

        return $this->assertSuccess($endpoint, $response);
    }

    /**
     * POST (JSON body) request that asserts a successful error_code.
     */
    private function post(string $endpoint, array $payload = []): Collection
    {
        $response = $this->send('POST', $endpoint, json: $payload);

        return $this->assertSuccess($endpoint, $response);
    }

    /**
     * GET request that returns the raw response (used by endpoints that do
     * not include an error_code field, e.g. STK).
     */
    private function getRaw(string $endpoint, array $query = []): Collection
    {
        return $this->send('GET', $endpoint, query: $query);
    }

    /**
     * POST request that returns the raw response (used by STK and get_status).
     */
    private function postRaw(string $endpoint, array|string $payload = []): Collection
    {
        return $this->send('POST', $endpoint, json: $payload);
    }

    /**
     * @throws DinstarException when the HTTP request itself fails.
     */
    private function send(
        string $method,
        string $endpoint,
        array $query = [],
        array|string $json = [],
    ): Collection {
        try {
            $request = $this->client();

            if ($query !== []) {
                $request = $request->withQueryParameters($query);
            }

            $response = match (strtoupper($method)) {
                'GET' => $request->get($this->url.$endpoint),
                'POST' => $request->post($this->url.$endpoint, $json),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            return $response->throw()->collect();
        } catch (RequestException $e) {
            throw DinstarException::transport($endpoint, $e);
        }
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->withHeaders(['Accept' => 'application/json'])
            ->withOptions(['verify' => $this->verify])
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, throw: false)
            ->asJson();
    }

    /**
     * Verify that the response carries a success error_code; otherwise raise.
     */
    private function assertSuccess(string $endpoint, Collection $response): Collection
    {
        $errorCode = $response->get('error_code');

        if ($errorCode === null) {
            throw DinstarException::malformed($endpoint, 'response is missing "error_code"');
        }

        $code = (int) $errorCode;

        if (! in_array($code, self::SUCCESS_CODES, true)) {
            throw DinstarException::api($endpoint, $code, $response->all());
        }

        return $response;
    }

    private function formatDate(DateTimeInterface|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(self::DATE_FORMAT);
        }

        return $value;
    }

    /**
     * Convert an array of integers/strings to a comma-separated value, or
     * null when the input is null/empty.
     *
     * @param  array<int, int|string>|null  $values
     */
    private function csv(?array $values): ?string
    {
        if ($values === null || $values === []) {
            return null;
        }

        return implode(',', $values);
    }

    private function enumValue(BackedEnum|string $value): string
    {
        return $value instanceof BackedEnum ? (string) $value->value : $value;
    }
}
