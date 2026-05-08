<?php

declare(strict_types=1);

namespace Logtrace;

class Logtrace extends Client
{
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
     *   geo_ip_location?: string,
     *   metadata?: array<string, mixed>
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
     *   status: 'ACTIVE'|'INACTIVE'|'SUCCESSFUL'|'FAILED'|'EXPIRED',
     *   user_id?: string,
     *   username?: string,
     *   device_info?: string,
     *   ip_address?: string,
     *   location?: string,
     *   token?: string,
     *   metadata?: array<string, mixed>
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
     *   metadata?: array<string, mixed>
     * } $params
     */
    public function createAuditLog(array $params): APIResponse
    {
        return $this->post('/audit-logs', $params);
    }
}
