
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
        public readonly string $ipAddress,
        public readonly string $operatingSystem,
        public readonly string $clientUserAgent,
        /** @var array<string, string> */
        public readonly array  $requestHeaders  = [],
        public readonly string $geoIpLocation   = '',
        public readonly string $requestDuration = '',
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
            'ip_address'       => $this->ipAddress,
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
    public ?RequestDetails $requestDetails = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $actionName,
        public readonly string $httpMethod,
        public readonly int    $httpStatus,
        public readonly string $clientIp,
        public readonly string $clientUserAgent,
        public readonly string $userId       = '',
        public readonly string $userName     = '',
        public readonly string $httpEndpoint = '',
        public readonly string $type         = '',
        public readonly string $geoIpLocation= '',
        public readonly array  $metadata     = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'action_name'       => $this->actionName,
            'http_method'       => $this->httpMethod,
            'http_status'       => $this->httpStatus,
            'client_ip'         => $this->clientIp,
            'client_user_agent' => $this->clientUserAgent,
        ];

        if ($this->userId)        $data['user_id']         = $this->userId;
        if ($this->userName)      $data['username']        = $this->userName;
        if ($this->httpEndpoint)  $data['http_endpoint']   = $this->httpEndpoint;
        if ($this->type)          $data['type']            = $this->type;
        if ($this->geoIpLocation) $data['geo_ip_location'] = $this->geoIpLocation;
        if ($this->metadata)      $data['metadata']        = $this->metadata;
        if ($this->requestDetails !== null) {
            $data['request_details'] = $this->requestDetails->toArray();
        }

        return $data;
    }
}

final class CreateSessionRequest
{
    public ?RequestDetails $requestDetails = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $loginAt,
        public readonly string $status,
        public readonly string $userId     = '',
        public readonly string $userName   = '',
        public readonly string $deviceInfo = '',
        public readonly string $ipAddress  = '',
        public readonly string $location   = '',
        public readonly string $token      = '',
        public readonly array  $metadata   = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'login_at' => $this->loginAt,
            'status'   => $this->status,
        ];

        if ($this->userId)     $data['user_id']    = $this->userId;
        if ($this->userName)   $data['username']   = $this->userName;
        if ($this->deviceInfo) $data['device_info']= $this->deviceInfo;
        if ($this->ipAddress)  $data['ip_address'] = $this->ipAddress;
        if ($this->location)   $data['location']   = $this->location;
        if ($this->token)      $data['token']       = $this->token;
        if ($this->metadata)   $data['metadata']   = $this->metadata;
        if ($this->requestDetails !== null) {
            $data['request_details'] = $this->requestDetails->toArray();
        }

        return $data;
    }
}

final class CreateAuditLogRequest
{
    public ?RequestDetails $requestDetails = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $action,
        public readonly string $timestamp,
        public readonly string $userId    = '',
        public readonly string $userName  = '',
        public readonly string $ipAddress = '',
        public readonly string $requestId = '',
        public readonly array  $metadata  = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'action'    => $this->action,
            'timestamp' => $this->timestamp,
        ];

        if ($this->userId)    $data['user_id']    = $this->userId;
        if ($this->userName)  $data['username']   = $this->userName;
        if ($this->ipAddress) $data['ip_address'] = $this->ipAddress;
        if ($this->requestId) $data['request_id'] = $this->requestId;
        if ($this->metadata)  $data['metadata']   = $this->metadata;
        if ($this->requestDetails !== null) {
            $data['request_details'] = $this->requestDetails->toArray();
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
