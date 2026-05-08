<?php

declare(strict_types=1);

namespace Logtrace;

interface HttpTransportInterface
{
    /**
     * @param  string[] $headers
     * @return array{statusCode: int, body: string|false, error: string}
     */
    public function send(string $url, string $payload, array $headers): array;
}

class CurlTransport implements HttpTransportInterface
{
    public function send(string $url, string $payload, array $headers): array
    {
        $ch = curl_init($url);

        if ($ch === false) {
            return ['statusCode' => 0, 'body' => false, 'error' => 'Failed to initialize cURL'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $body       = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error      = curl_error($ch);
        curl_close($ch);

        return ['statusCode' => $statusCode, 'body' => $body, 'error' => $error];
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

class Client
{
    private const DEFAULT_BASE_URL = 'https://api.logtracehq.com/v1/developers';

    public function __construct(
        private readonly string $apiKey,
        private readonly HttpTransportInterface $transport = new CurlTransport(),
    ) {}

    protected function post(string $path, array $body): APIResponse
    {
        $payload = json_encode(
            array_filter($body, fn($v) => $v !== null),
            JSON_THROW_ON_ERROR,
        );

        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
        ];

        $result     = $this->transport->send(self::DEFAULT_BASE_URL . $path, $payload, $headers);
        $statusCode = $result['statusCode'];

        if ($result['body'] === false) {
            throw new LogtraceException(0, 'Request failed: ' . $result['error']);
        }

        /** @var array{message?: string, statusCode?: int} $data */
        $data = json_decode((string) $result['body'], true) ?? [];

        if ($statusCode >= 400) {
            throw new LogtraceException($statusCode, $data['message'] ?? 'Unknown error');
        }

        return new APIResponse($data['message'] ?? '', $statusCode);
    }
}
