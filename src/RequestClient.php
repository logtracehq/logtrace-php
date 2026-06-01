
<?php

declare(strict_types=1);

namespace Logtrace;

/**
 * A thin wrapper around {@see Client} that automatically attaches
 * request details (method, endpoint, IP, headers, status) before
 * sending each API call.
 *
 * Obtain an instance via {@see RequestClient::fromRequest()} inside a
 * middleware, or construct one directly for non-HTTP contexts.
 */
final class RequestClient
{
    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(
        private readonly Client  $client,
        private readonly string  $method          = '',
        private readonly string  $endpoint        = '',
        private readonly string  $clientIp        = '',
        private readonly string  $userAgent       = '',
        private readonly string  $operatingSystem = '',
        private readonly mixed   $getStatus       = null,
    ) {}


    /** @param array<string, string> $headers */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }


    public function createEvent(CreateEventRequest $req): ApiResponse
    {
        $req->requestDetails = $this->buildRequestDetails();
        return $this->client->createEvent($req);
    }

    public function createSession(CreateSessionRequest $req): ApiResponse
    {
        $req->requestDetails = $this->buildRequestDetails();
        return $this->client->createSession($req);
    }

    public function createAuditLog(CreateAuditLogRequest $req): ApiResponse
    {
        $req->requestDetails = $this->buildRequestDetails();
        return $this->client->createAuditLog($req);
    }


  
    private function buildRequestDetails(): RequestDetails
    {
        $status = is_callable($this->getStatus) ? ($this->getStatus)() : 0;

        return new RequestDetails(
            timestamp:       (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            httpMethod:      $this->method,
            httpEndpoint:    $this->endpoint,
            httpStatusCode:  $status,
            ipAddress:       $this->clientIp,
            operatingSystem: $this->operatingSystem,
            clientUserAgent: $this->userAgent,
            requestHeaders:  $this->headers,
        );
    }
}
