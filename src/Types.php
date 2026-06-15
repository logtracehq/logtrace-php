
<?php

declare(strict_types=1);

namespace Logtrace;

/**
 * Arbitrary key-value metadata attached to any request.
 * Alias for array<string, mixed>.
 */
final class RequestDetails
{
    public function __construct(
        public readonly string $timestamp,
        public readonly string $httpMethod,
        public readonly string $httpEndpoint,
        public readonly int    $httpStatusCode,
        public readonly string $ip_address,
        public readonly string $operatingSystem,
        public readonly string $clientUserAgent,
        /** @var array<string, string> */
        public readonly array  $requestHeaders  = [],
        public readonly string $geoIpLocation   = '',
        public readonly string $tim = '',
        public readonly string $requestId       = '',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'timestamp'        => $this->timestamp,
            'http_method'      => $this->httpMethod,
            'http_endpoint'    => $this->httpEndpoint,
            'http_status_code' => $this->httpStatusCode,
            'ip_address'       => $this->ip_address,
            'operating_system' => $this->operatingSystem,
            'client_user_agent'=> $this->clientUserAgent,
            'request_headers'  => $this->requestHeaders,
            'geo_ip_location'  => $this->geoIpLocation,
            'request_duration' => $this->requestDuration,
            'request_id'       => $this->requestId,
        ];
    }
}

final class CreateEventRequest
{
    public ?RequestDetails $request_details = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $name,
        public readonly string $httpMethod,
        public readonly int    $httpStatus,
        public readonly string $clientIp,
        public readonly string $clientUserAgent,
        public readonly string $user_id       = '',
        public readonly string $username     = '',
        public readonly string $httpEndpoint = '',
        public readonly string $type         = '',
        public readonly string $geoIpLocation= '',
        public readonly array  $metadata     = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'name'       => $this->name,
            'http_method'       => $this->httpMethod,
            'http_status'       => $this->httpStatus,
            'client_ip'         => $this->clientIp,
            'client_user_agent' => $this->clientUserAgent,
        ];

        if ($this->user_id)        $data['user_id']         = $this->user_id;
        if ($this->username)      $data['username']        = $this->username;
        if ($this->httpEndpoint)  $data['http_endpoint']   = $this->httpEndpoint;
        if ($this->type)          $data['type']            = $this->type;
        if ($this->geoIpLocation) $data['geo_ip_location'] = $this->geoIpLocation;
        if ($this->metadata)      $data['metadata']        = $this->metadata;
        if ($this->request_details !== null) {
            $data['request_details'] = $this->request_details->toArray();
        }

        return $data;
    }
}

final class CreateSessionRequest
{
    public ?RequestDetails $request_details = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $login_at,
        public readonly string $status,
        public readonly string $user_id     = '',
        public readonly string $username   = '',
        public readonly string $ip_address  = '',
        public readonly string $location   = '',
        public readonly string $token      = '',
        public readonly array  $metadata   = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'login_at' => $this->login_at,
            'status'   => $this->status,
        ];

        if ($this->user_id)     $data['user_id']    = $this->user_id;
        if ($this->username)   $data['username']   = $this->username;
        if ($this->ip_address)  $data['ip_address'] = $this->ip_address;
        if ($this->location)   $data['location']   = $this->location;
        if ($this->token)      $data['token']       = $this->token;
        if ($this->metadata)   $data['metadata']   = $this->metadata;
        if ($this->request_details !== null) {
            $data['request_details'] = $this->request_details->toArray();
        }

        return $data;
    }
}

final class CreateAuditLogRequest
{
    public ?RequestDetails $request_details = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $name,
        public readonly string $timestamp,
        public readonly string $user_id    = '',
        public readonly string $username  = '',
        public readonly string $ip_address = '',
        public readonly string $requestId = '',
        public readonly array  $metadata  = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'name'    => $this->name,
            'timestamp' => $this->timestamp,
        ];

        if ($this->user_id)    $data['user_id']    = $this->user_id;
        if ($this->username)  $data['username']   = $this->username;
        if ($this->ip_address) $data['ip_address'] = $this->ip_address;
        if ($this->requestId) $data['request_id'] = $this->requestId;
        if ($this->metadata)  $data['metadata']   = $this->metadata;
        if ($this->request_details !== null) {
            $data['request_details'] = $this->request_details->toArray();
        }

        return $data;
    }
}

final class ApiResponse
{
    public function __construct(
        public readonly string $message,
        public readonly int    $statusCode,
    ) {}
}
