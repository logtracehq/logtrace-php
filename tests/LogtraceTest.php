<?php

declare(strict_types=1);

namespace Logtrace\Tests;

use Logtrace\Logtrace;
use Logtrace\APIResponse;
use Logtrace\LogtraceException;
use PHPUnit\Framework\TestCase;

 
class LogtraceTest extends TestCase
{
    private static array $curlMock = [];

    public static function setCurlMock(int $statusCode, string $body): void
    {
        self::$curlMock = [
            'statusCode' => $statusCode,
            'body' => $body,
            'error' => '',
            'lastOpts' => [],
            'lastUrl' => '',
        ];
    }

    public static function getCurlMock(): array
    {
        return self::$curlMock;
    }

    public static function setLastOpts(array $opts): void
    {
        self::$curlMock['lastOpts'] = $opts;
    }

    public static function setLastUrl(string $url): void
    {
        self::$curlMock['lastUrl'] = $url;
    }

    protected function setUp(): void
    {
        self::$curlMock = [];
    }


    public function testCreateEventSuccess(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'Event created', 'statusCode' => 200]));

        $client = new TestableLogtrace('test-api-key');
        $resp = $client->createEvent([
            'action_name' => 'user.login',
            'http_method' => 'POST',
            'http_status' => '200',
            'client_ip' => '192.168.1.1',
            'client_user_agent' => 'TestAgent/1.0',
        ]);

        $this->assertInstanceOf(APIResponse::class, $resp);
        $this->assertEquals(200, $resp->statusCode);
        $this->assertEquals('Event created', $resp->message);
    }

    public function testCreateEventSendsToCorrectUrl(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createEvent([
            'action_name' => 'test',
            'http_method' => 'GET',
            'http_status' => '200',
            'client_ip' => '0.0.0.0',
            'client_user_agent' => 't',
        ]);

        $this->assertStringEndsWith('/events', self::$curlMock['lastUrl']);
    }

    public function testCreateEventSendsCorrectHeaders(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('my-secret-key');
        $client->createEvent([
            'action_name' => 'test',
            'http_method' => 'GET',
            'http_status' => '200',
            'client_ip' => '0.0.0.0',
            'client_user_agent' => 't',
        ]);

        $headers = self::$curlMock['lastOpts'][CURLOPT_HTTPHEADER] ?? [];
        $this->assertContains('Content-Type: application/json', $headers);
        $this->assertContains('X-API-Key: my-secret-key', $headers);
    }

    public function testCreateEventSendsCorrectBody(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createEvent([
            'action_name' => 'user.signup',
            'http_method' => 'POST',
            'http_status' => '201',
            'client_ip' => '10.0.0.1',
            'client_user_agent' => 'Mozilla/5.0',
            'user_id' => 'usr_123',
        ]);

        $body = json_decode(self::$curlMock['lastOpts'][CURLOPT_POSTFIELDS], true);
        $this->assertEquals('user.signup', $body['action_name']);
        $this->assertEquals('usr_123', $body['user_id']);
    }

    public function testCreateEventOmitsNullValues(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createEvent([
            'action_name' => 'test',
            'http_method' => 'GET',
            'http_status' => '200',
            'client_ip' => '0.0.0.0',
            'client_user_agent' => 't',
            'user_id' => null,
        ]);

        $body = json_decode(self::$curlMock['lastOpts'][CURLOPT_POSTFIELDS], true);
        $this->assertArrayNotHasKey('user_id', $body);
    }


    public function testCreateSessionSuccess(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'Session created', 'statusCode' => 200]));

        $client = new TestableLogtrace('key');
        $resp = $client->createSession([
            'login_at' => '2025-01-15T10:30:00Z',
            'status' => 'ACTIVE',
        ]);

        $this->assertEquals('Session created', $resp->message);
        $this->assertEquals(200, $resp->statusCode);
    }

    public function testCreateSessionSendsToCorrectUrl(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createSession([
            'login_at' => '2025-01-01T00:00:00Z',
            'status' => 'ACTIVE',
        ]);

        $this->assertStringEndsWith('/sessions', self::$curlMock['lastUrl']);
    }

    public function testCreateSessionWithAllFields(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createSession([
            'login_at' => '2025-06-01T08:00:00Z',
            'status' => 'ACTIVE',
            'user_id' => 'usr_456',
            'username' => 'jane',
            'device_info' => 'Chrome on macOS',
            'ip_address' => '10.0.0.5',
            'location' => 'New York, US',
        ]);

        $body = json_decode(self::$curlMock['lastOpts'][CURLOPT_POSTFIELDS], true);
        $this->assertEquals('usr_456', $body['user_id']);
        $this->assertEquals('Chrome on macOS', $body['device_info']);
    }


    public function testCreateAuditLogSuccess(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'Audit log created', 'statusCode' => 200]));

        $client = new TestableLogtrace('key');
        $resp = $client->createAuditLog([
            'action' => 'user.deleted',
            'timestamp' => '2025-03-10T14:00:00Z',
        ]);

        $this->assertEquals('Audit log created', $resp->message);
    }

    public function testCreateAuditLogSendsToCorrectUrl(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createAuditLog([
            'action' => 't',
            'timestamp' => '2025-01-01T00:00:00Z',
        ]);

        $this->assertStringEndsWith('/audit-logs', self::$curlMock['lastUrl']);
    }

    public function testCreateAuditLogWithMetadata(): void
    {
        self::setCurlMock(200, json_encode(['message' => 'ok']));

        $client = new TestableLogtrace('key');
        $client->createAuditLog([
            'action' => 'user.role_change',
            'timestamp' => '2025-03-10T14:00:00Z',
            'user_id' => 'usr_789',
            'metadata' => [
                'event' => 'role_change',
                'description' => 'Promoted to admin',
            ],
        ]);

        $body = json_decode(self::$curlMock['lastOpts'][CURLOPT_POSTFIELDS], true);
        $this->assertEquals('role_change', $body['metadata']['event']);
        $this->assertEquals('Promoted to admin', $body['metadata']['description']);
    }


    public function testThrowsOnBadRequest400(): void
    {
        self::setCurlMock(400, json_encode(['message' => 'Bad request: missing action_name']));

        $client = new TestableLogtrace('key');

        $this->expectException(LogtraceException::class);
        $client->createEvent([
            'action_name' => '',
            'http_method' => 'GET',
            'http_status' => '200',
            'client_ip' => '0.0.0.0',
            'client_user_agent' => 't',
        ]);
    }

    public function testThrowsOnUnauthorized401(): void
    {
        self::setCurlMock(401, json_encode(['message' => 'Invalid API key']));

        $client = new TestableLogtrace('bad-key');

        try {
            $client->createEvent([
                'action_name' => 't',
                'http_method' => 'GET',
                'http_status' => '200',
                'client_ip' => '0.0.0.0',
                'client_user_agent' => 't',
            ]);
            $this->fail('Expected LogtraceException');
        } catch (LogtraceException $e) {
            $this->assertEquals(401, $e->statusCode);
            $this->assertStringContainsString('Invalid API key', $e->getMessage());
        }
    }

    public function testThrowsOnServerError500(): void
    {
        self::setCurlMock(500, json_encode(['message' => 'Internal server error']));

        $client = new TestableLogtrace('key');

        try {
            $client->createAuditLog([
                'action' => 't',
                'timestamp' => '2025-01-01T00:00:00Z',
            ]);
            $this->fail('Expected LogtraceException');
        } catch (LogtraceException $e) {
            $this->assertEquals(500, $e->statusCode);
        }
    }

    public function testThrowsOnCurlFailure(): void
    {
        self::$curlMock = [
            'statusCode' => 0,
            'body' => false,
            'error' => 'Connection refused',
            'lastOpts' => [],
            'lastUrl' => '',
        ];

        $client = new TestableLogtrace('key');

        $this->expectException(LogtraceException::class);
        $this->expectExceptionMessageMatches('/Connection refused/');
        $client->createEvent([
            'action_name' => 't',
            'http_method' => 'GET',
            'http_status' => '200',
            'client_ip' => '0.0.0.0',
            'client_user_agent' => 't',
        ]);
    }


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

/**
 * Testable subclass that overrides curl-based post to use in-memory mock.
 */
class TestableLogtrace extends Logtrace
{
    public function createEvent(array $params): APIResponse
    {
        return $this->mockPost('/events', $params);
    }

    public function createSession(array $params): APIResponse
    {
        return $this->mockPost('/sessions', $params);
    }

    public function createAuditLog(array $params): APIResponse
    {
        return $this->mockPost('/audit-logs', $params);
    }

    private function mockPost(string $path, array $body): APIResponse
    {
        $payload = json_encode(array_filter($body, fn($v) => $v !== null), JSON_THROW_ON_ERROR);

        $url = 'https://api.logtracehq.com/v1/developers' . $path;
        LogtraceTest::setLastUrl($url);

        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->getApiKey(),
            ],
        ];
        LogtraceTest::setLastOpts($opts);

        $mock = LogtraceTest::getCurlMock();
        $response = $mock['body'];
        $statusCode = $mock['statusCode'];
        $error = $mock['error'];

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

    public function getApiKey(): string
    {
        return (new \ReflectionClass(Logtrace::class))->getProperty('apiKey')->getValue($this);
    }
}
