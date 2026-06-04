
<?php

declare(strict_types=1);

namespace Logtrace;

use CurlHandle;
use RuntimeException;

final class Client
{
    private const DEFAULT_BASE_URL  = 'https://api.logtracehq.com/v1/developers';
    private const DEFAULT_TIMEOUT_S = 10;

    private readonly string $baseUrl;
    private readonly int    $timeoutSeconds;

    /**
     * @throws \InvalidArgumentException if $apiKey is empty
     */
    public function __construct(
        private readonly string $apiKey,
        int    $timeoutSeconds = self::DEFAULT_TIMEOUT_S,
    ) {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('logtrace: API key is required');
        }
        $this->baseUrl        = self::DEFAULT_BASE_URL;
        $this->timeoutSeconds = $timeoutSeconds;
    }


    public function createEvent(CreateEventRequest $req): ApiResponse
    {
        return $this->post('/events', $req->toArray());
    }

    public function createSession(CreateSessionRequest $req): ApiResponse
    {
        return $this->post('/sessions', $req->toArray());
    }

    public function createAuditLog(CreateAuditLogRequest $req): ApiResponse
    {
        return $this->post('/audit-logs', $req->toArray());
    }


    /**
     * @param array<string, mixed> $body
     * @throws LogtraceException on 4xx / 5xx responses
     * @throws RuntimeException  on network / curl errors
     */
    private function post(string $path, array $body): ApiResponse
    {
        $url     = $this->baseUrl . $path;
        $payload = json_encode($body, JSON_THROW_ON_ERROR);

        $ch = curl_init();

