<?php

declare(strict_types=1);

namespace Logtrace;

class Logtrace
{
    private string $apiKey;

    private const DEFAULT_BASE_URL = 'https://api.logtracehq.com/v1/developers';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Send an event to Logtrace.
     *
     * @param array{
     *   action_name: string,
     *   http_method: string,
     *   http_status: string,
     *   client_ip: string,
     *   client_user_agent: string,
     *   user_id?: string,
     *   username?: string,
     *   http_endpoint?: string,
     *   type?: string,
     *   geo_ip_location?: string
     * } $params
     */
    public function createEvent(array $params): APIResponse
    {
        return $this->post('/events', $params);
    }

    /**
     * Send a session to Logtrace.
     *
     * @param array{
     *   login_at: string,
     *   status: 'ACTIVE'|'INACTIVE',
     *   user_id?: string,
     *   username?: string,
     *   device_info?: string,
     *   ip_address?: string,
     *   location?: string
     * } $params
     */
    public function createSession(array $params): APIResponse
    {
        return $this->post('/sessions', $params);
    }

    /**
     * Send an audit log to Logtrace.
     *
     * @param array{
     *   action: string,
     *   timestamp: string,
     *   user_id?: string,
     *   username?: string,
     *   ip_address?: string,
     *   request_id?: string,
     *   metadata?: array{event?: string, type?: string, description?: string}
     * } $params
     */
    public function createAuditLog(array $params): APIResponse
    {
        return $this->post('/audit-logs', $params);
    }

    private function post(string $path, array $body): APIResponse
    {
        $payload = json_encode(array_filter($body, fn($v) => $v !== null), JSON_THROW_ON_ERROR);

        $ch = curl_init(self::DEFAULT_BASE_URL . $path);

        if ($ch === false) {
            throw new LogtraceException(0, 'Failed to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new LogtraceException(0, 'Request failed: ' . $error);
        }

        /** @var array{message?: string, statusCode?: int} $data */
        $data = json_decode((string) $response, true) ?? [];

        if ($statusCode >= 400) {
            throw new LogtraceException($statusCode, $data['message'] ?? 'Unknown error');
        }

        return new APIResponse($data['message'] ?? '', $statusCode);
    }
}

class APIResponse
{
    public function __construct(
        public readonly string $message,
        public readonly int $statusCode,
    ) {}
}

class LogtraceException extends \Exception
{
    public readonly int $statusCode;

    public function __construct(int $statusCode, string $message)
    {
        $this->statusCode = $statusCode;
        parent::__construct("logtrace: {$statusCode} - {$message}", $statusCode);
    }
}
