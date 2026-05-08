<?php

declare(strict_types=1);

namespace Logtrace\Tests;

use Logtrace\APIResponse;
use Logtrace\HttpTransportInterface;
use Logtrace\Logtrace;
use Logtrace\LogtraceException;
use PHPUnit\Framework\TestCase;

class MockTransport implements HttpTransportInterface
{
    public string $lastUrl = '';
    public string $lastPayload = '';
    /** @var string[] */
    public array $lastHeaders = [];

    public int $statusCode = 200;
    public string|false $body = '';
    public string $error = '';

    /** @return array{statusCode: int, body: string|false, error: string} */
    public function send(string $url, string $payload, array $headers): array
    {
        $this->lastUrl     = $url;
        $this->lastPayload = $payload;
        $this->lastHeaders = $headers;

        return ['statusCode' => $this->statusCode, 'body' => $this->body, 'error' => $this->error];
    }
}

class LogtraceTest extends TestCase
{
    private function makeTransport(int $statusCode, string|false $body, string $error = ''): MockTransport
    {
        $t             = new MockTransport();
        $t->statusCode = $statusCode;
        $t->body       = $body;
        $t->error      = $error;
        return $t;
    }

    // -------------------------------------------------------------------------
    // createEvent
    // -------------------------------------------------------------------------

    public function testCreateEventSuccess(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'Event created', 'statusCode' => 200]));
        $client    = new Logtrace('test-api-key', $transport);

        $resp = $client->createEvent([
            'action_name'       => 'user.login',
            'http_method'       => 'POST',
            'http_status'       => '200',
            'client_ip'         => '192.168.1.1',
            'client_user_agent' => 'TestAgent/1.0',
        ]);

        $this->assertInstanceOf(APIResponse::class, $resp);
        $this->assertEquals(200, $resp->statusCode);
        $this->assertEquals('Event created', $resp->message);
    }

    public function testCreateEventSendsToCorrectUrl(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createEvent([
            'action_name' => 'test', 'http_method' => 'GET', 'http_status' => '200',
            'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
        ]);

        $this->assertStringEndsWith('/v1/developers/events', $transport->lastUrl);
    }

    public function testCreateEventSendsCorrectHeaders(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('my-secret-key', $transport);

        $client->createEvent([
            'action_name' => 'test', 'http_method' => 'GET', 'http_status' => '200',
            'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
        ]);

        $this->assertContains('Content-Type: application/json', $transport->lastHeaders);
        $this->assertContains('X-API-Key: my-secret-key', $transport->lastHeaders);
    }

    public function testCreateEventSendsCorrectBody(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createEvent([
            'action_name'       => 'user.signup',
            'http_method'       => 'POST',
            'http_status'       => '201',
            'client_ip'         => '10.0.0.1',
            'client_user_agent' => 'Mozilla/5.0',
            'user_id'           => 'usr_123',
        ]);

        $body = json_decode($transport->lastPayload, true);
        $this->assertEquals('user.signup', $body['action_name']);
        $this->assertEquals('usr_123', $body['user_id']);
    }

    public function testCreateEventOmitsNullValues(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createEvent([
            'action_name' => 'test', 'http_method' => 'GET', 'http_status' => '200',
            'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
            'user_id' => null,
        ]);

        $body = json_decode($transport->lastPayload, true);
        $this->assertArrayNotHasKey('user_id', $body);
    }

    // -------------------------------------------------------------------------
    // createSession
    // -------------------------------------------------------------------------

    public function testCreateSessionSuccess(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'Session created', 'statusCode' => 200]));
        $client    = new Logtrace('key', $transport);

        $resp = $client->createSession(['login_at' => '2025-01-15T10:30:00Z', 'status' => 'ACTIVE']);

        $this->assertEquals('Session created', $resp->message);
        $this->assertEquals(200, $resp->statusCode);
    }

    public function testCreateSessionSendsToCorrectUrl(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createSession(['login_at' => '2025-01-01T00:00:00Z', 'status' => 'ACTIVE']);

        $this->assertStringEndsWith('/v1/developers/sessions', $transport->lastUrl);
    }

    public function testCreateSessionWithAllFields(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createSession([
            'login_at'    => '2025-06-01T08:00:00Z',
            'status'      => 'ACTIVE',
            'user_id'     => 'usr_456',
            'username'    => 'jane',
            'device_info' => 'Chrome on macOS',
            'ip_address'  => '10.0.0.5',
            'location'    => 'New York, US',
        ]);

        $body = json_decode($transport->lastPayload, true);
        $this->assertEquals('usr_456', $body['user_id']);
        $this->assertEquals('Chrome on macOS', $body['device_info']);
    }

    // -------------------------------------------------------------------------
    // createAuditLog
    // -------------------------------------------------------------------------

    public function testCreateAuditLogSuccess(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'Audit log created', 'statusCode' => 200]));
        $client    = new Logtrace('key', $transport);

        $resp = $client->createAuditLog(['action' => 'user.deleted', 'timestamp' => '2025-03-10T14:00:00Z']);

        $this->assertEquals('Audit log created', $resp->message);
    }

    public function testCreateAuditLogSendsToCorrectUrl(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createAuditLog(['action' => 't', 'timestamp' => '2025-01-01T00:00:00Z']);

        $this->assertStringEndsWith('/v1/developers/audit-logs', $transport->lastUrl);
    }

    public function testCreateAuditLogWithMetadata(): void
    {
        $transport = $this->makeTransport(200, json_encode(['message' => 'ok']));
        $client    = new Logtrace('key', $transport);

        $client->createAuditLog([
            'action'    => 'user.role_change',
            'timestamp' => '2025-03-10T14:00:00Z',
            'user_id'   => 'usr_789',
            'metadata'  => ['event' => 'role_change', 'description' => 'Promoted to admin'],
        ]);

        $body = json_decode($transport->lastPayload, true);
        $this->assertEquals('role_change', $body['metadata']['event']);
        $this->assertEquals('Promoted to admin', $body['metadata']['description']);
    }

    // -------------------------------------------------------------------------
    // Error handling
    // -------------------------------------------------------------------------

    public function testThrowsOnBadRequest400(): void
    {
        $transport = $this->makeTransport(400, json_encode(['message' => 'Bad request: missing action_name']));
        $client    = new Logtrace('key', $transport);

        $this->expectException(LogtraceException::class);
        $client->createEvent([
            'action_name' => '', 'http_method' => 'GET', 'http_status' => '200',
            'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
        ]);
    }

    public function testThrowsOnUnauthorized401(): void
    {
        $transport = $this->makeTransport(401, json_encode(['message' => 'Invalid API key']));
        $client    = new Logtrace('bad-key', $transport);

        try {
            $client->createEvent([
                'action_name' => 't', 'http_method' => 'GET', 'http_status' => '200',
                'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
            ]);
            $this->fail('Expected LogtraceException');
        } catch (LogtraceException $e) {
            $this->assertEquals(401, $e->statusCode);
            $this->assertStringContainsString('Invalid API key', $e->getMessage());
        }
    }

    public function testThrowsOnServerError500(): void
    {
        $transport = $this->makeTransport(500, json_encode(['message' => 'Internal server error']));
        $client    = new Logtrace('key', $transport);

        try {
            $client->createAuditLog(['action' => 't', 'timestamp' => '2025-01-01T00:00:00Z']);
            $this->fail('Expected LogtraceException');
        } catch (LogtraceException $e) {
            $this->assertEquals(500, $e->statusCode);
        }
    }

    public function testThrowsOnTransportFailure(): void
    {
        $transport = $this->makeTransport(0, false, 'Connection refused');
        $client    = new Logtrace('key', $transport);

        $this->expectException(LogtraceException::class);
        $this->expectExceptionMessageMatches('/Connection refused/');
        $client->createEvent([
            'action_name' => 't', 'http_method' => 'GET', 'http_status' => '200',
            'client_ip' => '0.0.0.0', 'client_user_agent' => 't',
        ]);
    }

    // -------------------------------------------------------------------------
    // Type tests
    // -------------------------------------------------------------------------

    public function testExceptionProperties(): void
    {
        $e = new LogtraceException(404, 'Not found');
        $this->assertEquals(404, $e->statusCode);
        $this->assertStringContainsString('404', $e->getMessage());
        $this->assertStringContainsString('Not found', $e->getMessage());
    }

    public function testAPIResponseProperties(): void
    {
        $resp = new APIResponse('Success', 201);
        $this->assertEquals('Success', $resp->message);
        $this->assertEquals(201, $resp->statusCode);
    }
}
